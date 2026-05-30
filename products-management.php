<?php
declare(strict_types=1);

define('DA_APP', true);
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/admin-layout.php';

$categories = ['sidr' => 'السدر', 'flowers' => 'الزهور', 'talh' => 'الطلح', 'comb' => 'الشهد'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_require_login();
    csrf_require();
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            db()->prepare('UPDATE products SET is_active = 0 WHERE id = ?')->execute([$id]);
            $message = 'تم إخفاء المنتج.';
        }
    } elseif ($action === 'save') {
        $id = (int) ($_POST['id'] ?? 0);
        $name = trim((string) ($_POST['name'] ?? ''));
        $slug = trim((string) ($_POST['slug'] ?? '')) ?: preg_replace('/\s+/', '-', $name);
        $desc = trim((string) ($_POST['description'] ?? ''));
        $price = (float) ($_POST['price'] ?? 0);
        $category = (string) ($_POST['category'] ?? 'flowers');
        $image = trim((string) ($_POST['current_image'] ?? 'images/pro4.webp'));
        $bestseller = !empty($_POST['is_bestseller']) ? 1 : 0;
        $featured = !empty($_POST['is_featured']) ? 1 : 0;
        $sort = (int) ($_POST['sort_order'] ?? 0);

        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $_FILES['image']['tmp_name']);
            finfo_close($finfo);
            $allowed = ['image/jpeg', 'image/png', 'image/webp'];
            if (in_array($mime, $allowed, true) && $_FILES['image']['size'] <= MAX_RECEIPT_BYTES) {
                $ext = $mime === 'image/png' ? 'png' : ($mime === 'image/webp' ? 'webp' : 'jpg');
                $fname = 'prod_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
                if (move_uploaded_file($_FILES['image']['tmp_name'], UPLOAD_PRODUCTS . '/' . $fname)) {
                    $image = 'uploads/products/' . $fname;
                }
            }
        }

        if ($name !== '' && $price > 0) {
            if ($id > 0) {
                $stmt = db()->prepare(
                    'UPDATE products SET name=?, slug=?, description=?, price=?, image=?, category=?, is_bestseller=?, is_featured=?, sort_order=? WHERE id=?'
                );
                $stmt->execute([$name, $slug, $desc, $price, $image, $category, $bestseller, $featured, $sort, $id]);
            } else {
                $stmt = db()->prepare(
                    'INSERT INTO products (name, slug, description, price, image, category, is_bestseller, is_featured, sort_order) VALUES (?,?,?,?,?,?,?,?,?)'
                );
                $stmt->execute([$name, $slug, $desc, $price, $image, $category, $bestseller, $featured, $sort]);
            }
            if ($featured) {
                db()->exec('UPDATE products SET is_featured = 0');
                if ($id > 0) {
                    db()->prepare('UPDATE products SET is_featured = 1 WHERE id = ?')->execute([$id]);
                } else {
                    db()->prepare('UPDATE products SET is_featured = 1 WHERE slug = ?')->execute([$slug]);
                }
            }
            $message = 'تم حفظ المنتج.';
        }
    }
    header('Location: products-management.php?msg=' . urlencode($message));
    exit;
}

if (!empty($_GET['msg'])) {
    $message = (string) $_GET['msg'];
}

$editId = (int) ($_GET['edit'] ?? 0);
$edit = $editId ? get_product_by_id($editId) : null;
$products = get_products(false);

admin_header($edit ? 'تعديل منتج' : 'إدارة المنتجات', 'products');
?>
<?php if ($csrfErr = admin_flash_error()): ?><div class="admin-flash admin-flash-error"><?= e($csrfErr) ?></div><?php endif; ?>
<?php if ($message): ?><div class="admin-flash"><?= e($message) ?></div><?php endif; ?>

<div class="admin-grid-2">
    <section class="admin-panel">
        <h2><?= $edit ? 'تعديل' : 'إضافة منتج' ?></h2>
        <form method="post" enctype="multipart/form-data" class="admin-form">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
            <input type="hidden" name="current_image" value="<?= e($edit['image'] ?? 'images/pro4.webp') ?>">
            <label>الاسم <input type="text" name="name" required value="<?= e($edit['name'] ?? '') ?>"></label>
            <label>Slug <input type="text" name="slug" value="<?= e($edit['slug'] ?? '') ?>"></label>
            <label>الوصف <textarea name="description" rows="3" required><?= e($edit['description'] ?? '') ?></textarea></label>
            <label>السعر <input type="number" step="0.01" name="price" required value="<?= e((string) ($edit['price'] ?? '')) ?>"></label>
            <label>الفئة
                <select name="category">
                    <?php foreach ($categories as $k => $v): ?>
                        <option value="<?= e($k) ?>" <?= ($edit['category'] ?? '') === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>ترتيب <input type="number" name="sort_order" value="<?= (int) ($edit['sort_order'] ?? 0) ?>"></label>
            <label><input type="checkbox" name="is_bestseller" value="1" <?= !empty($edit['is_bestseller']) ? 'checked' : '' ?>> الأكثر مبيعاً</label>
            <label><input type="checkbox" name="is_featured" value="1" <?= !empty($edit['is_featured']) ? 'checked' : '' ?>> منتج الشهر</label>
            <label>صورة <input type="file" name="image" accept="image/jpeg,image/png,image/webp"></label>
            <button type="submit" class="btn-admin-primary">حفظ</button>
            <?php if ($edit): ?><a href="products-management.php" class="btn-admin-secondary">إلغاء</a><?php endif; ?>
        </form>
    </section>
    <section class="admin-panel">
        <h2>المنتجات</h2>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead><tr><th>الاسم</th><th>السعر</th><th>الفئة</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($products as $p): if (empty($p['is_active']) && isset($p['is_active'])) continue; ?>
                    <tr>
                        <td><?= e($p['name']) ?></td>
                        <td><?= number_format((float) $p['price'], 0) ?> دج</td>
                        <td><?= e(category_label($p['category'] ?? '')) ?></td>
                        <td>
                            <a href="?edit=<?= (int) $p['id'] ?>">تعديل</a>
                            <form method="post" style="display:inline" onsubmit="return confirm('إخفاء المنتج؟')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                <button type="submit" class="link-danger">حذف</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
<?php admin_footer(); ?>
