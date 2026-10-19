#!/bin/bash
set -u
cd "$(dirname "$0")/.."

DB="storage/da_honey_shop.sqlite"
BACKUP="/tmp/smoke_backup_$$.sqlite"
PORT=8766
BASE="http://127.0.0.1:$PORT"
SRV_PID=""
PASS=0
FAIL=0

cleanup() {
  echo ""
  echo "=== CLEANUP ==="
  [ -n "$SRV_PID" ] && { kill "$SRV_PID" 2>/dev/null; wait "$SRV_PID" 2>/dev/null; }
  [ -f "$BACKUP" ] && { cp "$BACKUP" "$DB"; rm -f "$BACKUP"; echo "DB restored from backup"; }
}
trap cleanup EXIT

assert_contains() {
  local name="$1" expected="$2" actual="$3"
  if echo "$actual" | grep -q "$expected"; then
    echo "PASS: $name"; PASS=$((PASS+1))
  else
    echo "FAIL: $name"
    echo "  expected: $expected"
    echo "  actual:   $actual"
    FAIL=$((FAIL+1))
  fi
}

phpq() { php -r "require 'config.php'; $1" 2>/dev/null; }

reset_rate_limit() {
  # Best-effort: retry because SQLite write locks may be held by dev/smoke servers
  for i in 1 2 3 4 5; do
    if php -r '
      try {
        require "config.php";
        db()->exec("DELETE FROM order_rate_limits");
        exit(0);
      } catch (Throwable $e) {
        exit(1);
      }
    ' 2>/dev/null; then
      return 0
    fi
    sleep 0.3
  done
  return 0
}

get_csrf() {
  local jar="$1"
  curl -sS -c "$jar" "$BASE/" > /tmp/page_$$.html
  grep -oP 'name="csrf_token" value="\K[a-f0-9]+' /tmp/page_$$.html | head -1
}

echo "=== PREFLIGHT ==="
[ -f "$DB" ] || { echo "FATAL: $DB missing"; exit 1; }
cp "$DB" "$BACKUP"
echo "DB backed up → $BACKUP"

phpq 'db()->exec("UPDATE products SET quantity_available=999 WHERE id=1");'
echo "Stock seeded: product id=1 → 999"
reset_rate_limit

php -S 127.0.0.1:$PORT > /tmp/smoke-server.log 2>&1 &
SRV_PID=$!
sleep 1
curl -sS -o /dev/null "$BASE/" || { echo "FATAL: server not up"; exit 1; }
echo "Server up: $BASE (PID $SRV_PID)"

# ========== TEST 1: VALID ORDER ==========
echo ""
echo "=== TEST 1: Valid order ==="
reset_rate_limit
JAR1="/tmp/jar1_$$.txt"
CSRF1=$(get_csrf "$JAR1")
BEFORE=$(phpq 'echo db()->query("SELECT COUNT(*) FROM orders")->fetchColumn();')
R1=$(curl -sS -b "$JAR1" -X POST "$BASE/submit-order.php" \
  -H "X-Requested-With: XMLHttpRequest" \
  -F "csrf_token=$CSRF1" \
  --form-string "product_name=عسل جبلي" \
  -F "quantity=1" \
  --form-string "customer_name=مستخدم تجريبي" \
  -F "customer_phone=0551234567" \
  --form-string "customer_address=123 شارع تجريبي، الجزائر" \
  -F "payment_method=cod")
echo "R1: $R1"
AFTER=$(phpq 'echo db()->query("SELECT COUNT(*) FROM orders")->fetchColumn();')
echo "orders: $BEFORE → $AFTER"
assert_contains "T1 success=true" '"success":true' "$R1"
if [ "$AFTER" -gt "$BEFORE" ]; then
  echo "PASS: T1 persisted in DB"; PASS=$((PASS+1))
else
  echo "FAIL: T1 no new order"; FAIL=$((FAIL+1))
fi

# ========== TEST 2: NO CSRF ==========
echo ""
echo "=== TEST 2: No CSRF ==="
reset_rate_limit
JAR2="/tmp/jar2_$$.txt"
curl -sS -c "$JAR2" "$BASE/" > /dev/null
R2=$(curl -sS -b "$JAR2" -X POST "$BASE/submit-order.php" \
  -H "X-Requested-With: XMLHttpRequest" \
  --form-string "product_name=عسل جبلي" \
  -F "quantity=1" \
  --form-string "customer_name=Test" \
  -F "customer_phone=0551234567" \
  --form-string "customer_address=addr" \
  -F "payment_method=cod")
echo "R2: $R2"
assert_contains "T2 CSRF rejected" 'انتهت صلاحية الجلسة' "$R2"

# ========== TEST 3: NO PRODUCT ==========
echo ""
echo "=== TEST 3: No product ==="
reset_rate_limit
JAR3="/tmp/jar3_$$.txt"
CSRF3=$(get_csrf "$JAR3")
R3=$(curl -sS -b "$JAR3" -X POST "$BASE/submit-order.php" \
  -H "X-Requested-With: XMLHttpRequest" \
  -F "csrf_token=$CSRF3" \
  -F "quantity=1" \
  --form-string "customer_name=Test" \
  -F "customer_phone=0551234567" \
  --form-string "customer_address=addr" \
  -F "payment_method=cod")
echo "R3: $R3"
assert_contains "T3 empty cart" 'سلة التسوق فارغة' "$R3"

# ========== TEST 4: INVALID PHONE ==========
echo ""
echo "=== TEST 4: Invalid phone ==="
reset_rate_limit
JAR4="/tmp/jar4_$$.txt"
CSRF4=$(get_csrf "$JAR4")
R4=$(curl -sS -b "$JAR4" -X POST "$BASE/submit-order.php" \
  -H "X-Requested-With: XMLHttpRequest" \
  -F "csrf_token=$CSRF4" \
  --form-string "product_name=عسل جبلي" \
  -F "quantity=1" \
  --form-string "customer_name=Test" \
  -F "customer_phone=123" \
  --form-string "customer_address=addr" \
  -F "payment_method=cod")
echo "R4: $R4"
assert_contains "T4 rejects invalid phone" 'رقم الهاتف' "$R4"

# ========== TEST 5: INVALID PAYMENT ==========
echo ""
echo "=== TEST 5: Invalid payment_method ==="
reset_rate_limit
JAR5="/tmp/jar5_$$.txt"
CSRF5=$(get_csrf "$JAR5")
R5=$(curl -sS -b "$JAR5" -X POST "$BASE/submit-order.php" \
  -H "X-Requested-With: XMLHttpRequest" \
  -F "csrf_token=$CSRF5" \
  --form-string "product_name=عسل جبلي" \
  -F "quantity=1" \
  --form-string "customer_name=Test" \
  -F "customer_phone=0551234567" \
  --form-string "customer_address=addr" \
  -F "payment_method=weird_method")
echo "R5: $R5"
assert_contains "T5 rejects invalid payment" 'طريقة دفع' "$R5"

echo ""
echo "=== SUMMARY ==="
echo "PASS: $PASS / FAIL: $FAIL"
[ "$FAIL" -eq 0 ]

