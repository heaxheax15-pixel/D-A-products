<?php
declare(strict_types=1);

if (extension_loaded('zlib')) {
    ob_start('ob_gzhandler');
} else {
    ob_start();
}

define('DA_APP', true);
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/pwa.php';

header('Vary: Accept-Encoding');

$waNum = runtime('CONTACT_WHATSAPP', CONTACT_WHATSAPP);
$waMsg = urlencode(runtime('WHATSAPP_GREETING', WHATSAPP_GREETING));
$siteUrl = SITE_URL ?: ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
$ogImg = $siteUrl . '/' . OG_IMAGE;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>سياسة الخصوصية وشروط الخدمة | D&A Product</title>
    <?php pwa_head_tags(); ?>
    <meta name="description" content="سياسة الخصوصية وشروط الخدمة والاستخدام لمتجر D&A Product – نحن نحترم خصوصيتك وحماية بيانات عملائنا من أولوياتنا.">
    <meta name="keywords" content="سياسة الخصوصية, شروط الخدمة, D&A Product, عسل، شروط الاستخدام">
    <meta property="og:title" content="سياسة الخصوصية | D&A Product">
    <meta property="og:description" content="نلتزم بحماية بيانات عملائنا والحفاظ على خصوصيتهم بأعلى معايير الأمان.">
    <meta property="og:image" content="<?= e($ogImg) ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="ar_SA">
    <link rel="icon" href="favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('assets/css/style.min.css') ?>">
</head>
<body>
    <header class="site-header" id="siteHeader">
        <div class="header-bg-logo" style="background-image:url(images/pro4.webp)" aria-hidden="true"></div>
        <div class="container header-inner">
            <a href="index.php" class="logo">
                <svg class="logo-bee bee-animated" viewBox="0 0 32 32" width="36" height="36" aria-hidden="true">
                    <ellipse cx="16" cy="18" rx="9" ry="7" fill="#FFAA00"/>
                    <rect x="8" y="14" width="16" height="3" fill="#3d2914" opacity=".5"/>
                    <rect x="8" y="19" width="16" height="3" fill="#3d2914" opacity=".5"/>
                    <circle cx="16" cy="10" r="5" fill="#F5A623"/>
                    <ellipse cx="11" cy="8" rx="4" ry="2" fill="rgba(255,255,255,.45)" transform="rotate(-30 11 8)"/>
                    <ellipse cx="21" cy="8" rx="4" ry="2" fill="rgba(255,255,255,.45)" transform="rotate(30 21 8)"/>
                </svg>
                <span class="logo-text">D&amp;A Product</span>
            </a>
            <button class="nav-toggle" type="button" aria-label="القائمة" aria-expanded="false"><span></span><span></span><span></span></button>
            <nav class="main-nav" id="mainNav">
                <a href="index.php">الرئيسية</a>
                <a href="index.php#products">منتجاتنا</a>
                <a href="index.php#story">قصتنا</a>
                <a href="index.php#order">اطلب الآن</a>
                <a href="privacy-policy.php" class="nav-cta">السياسة والشروط</a>
            </nav>
        </div>
    </header>

    <section class="hero" id="top" style="padding-top: 120px;">
        <div class="hero-overlay" style="opacity: 0.3;"></div>
        <div class="container hero-content">
            <h1>سياسة الخصوصية وشروط الخدمة</h1>
            <p class="hero-sub">نلتزم بحماية بيانات عملائنا والحفاظ على خصوصيتهم بأعلى معايير الأمان والشفافية.</p>
        </div>
    </section>

    <section class="section" style="padding: 60px 0;">
        <div class="container" style="max-width: 800px;">
            
            <!-- =================== المقدمة الترحيبية =================== -->
            <article class="policy-section">
                <h2>مقدمة ترحيبية</h2>
                <p>
                    أهلاً وسهلاً بك في متجر <strong>D&amp;A Product</strong>، متجر عسل طبيعي فاخر معصور على البارد من مناحلنا الجبلية. 
                    نحن نشعر بالمسؤولية تجاه ثقة عملائنا، وبالتالي <strong>نلتزم بحماية خصوصيتك والحفاظ على أمان بيانات عملائنا</strong>.
                </p>
                <p>
                    هذه الصفحة توضح كيفية نحصل على معلوماتك، كيف نحميها، وحقوقك في التحكم بها. الرجاء قراءة هذه السياسة بعناية قبل تقديم طلب.
                </p>
            </article>

            <!-- =================== جمع المعلومات =================== -->
            <article class="policy-section">
                <h2>جمع المعلومات</h2>
                <p>
                    عندما تقدم طلباً عبر متجرنا، نجمع فقط <strong>البيانات الضرورية لإتمام التوصيل والتواصل معك بشأن طلبك</strong>:
                </p>
                <ul style="margin: 20px 0; line-height: 1.8;">
                    <li><strong>الاسم الكامل:</strong> لتحديد المستقبل والتواصل الودود معك</li>
                    <li><strong>رقم الهاتف:</strong> لتأكيد الطلب، التواصل معك عبر الواتساب أو الاتصال المباشر</li>
                    <li><strong>العنوان (الولاية / المدينة / الحي):</strong> لتوصيل الطلب إلى عتبة باب منزلك</li>
                    <li><strong>ملاحظات الطلب (اختياري):</strong> لأي طلبات خاصة أو تعليقات إضافية</li>
                </ul>
                <p style="color: #666; font-size: 14px; margin-top: 15px;">
                    <strong>ملاحظة:</strong> لا نطلب كلمات المرور أو بيانات بطاقات ائتمان. طريقة الدفع الرئيسية لدينا هي "الدفع عند الاستلام" (كاش عند الاستقبال)، وكذلك التحويل البنكي (CCP / BaridiMob).
                </p>
            </article>

            <!-- =================== حماية البيانات =================== -->
            <article class="policy-section">
                <h2>حماية البيانات والأمان</h2>
                <p>
                    <strong>بيانات عملائنا محمية بأعلى معايير الأمان والتشفير:</strong>
                </p>
                <ul style="margin: 20px 0; line-height: 1.8;">
                    <li>✅ <strong>التشفير الآمن (HTTPS):</strong> جميع البيانات المرسلة من متصفحك إلى خادمنا مشفرة بتشفير SSL/TLS من الطراز العسكري</li>
                    <li>✅ <strong>بدون مشاركة مع أطراف ثالثة:</strong> لا نبيع أو نشارك بيانات عملائنا مع شركات إعلانية أو جهات غير موثوقة</li>
                    <li>✅ <strong>استثناء التوصيل:</strong> نشارك معلومات الشحن (الاسم والعنوان والهاتف) فقط مع شركة التوصيل الموثوقة لإتمام عملية الشحن</li>
                    <li>✅ <strong>حماية من الاختراق:</strong> خادمنا محمي بجدران حماية متقدمة (Firewalls) ومراقبة أمنية مستمرة</li>
                    <li>✅ <strong>لا نخزن كلمات مرور:</strong> كل عملية دفع تتم عبر بوابات دفع محمية معتمدة (وليس على خادمنا)</li>
                </ul>
                <p style="color: #2c5f2d; background: #e8f5e9; padding: 15px; border-right: 4px solid #4caf50; margin-top: 15px; border-radius: 4px;">
                    <strong>🔒 ضماننا:</strong> بيانات عملائنا آمنة تماماً ولا يتم الوصول إليها إلا من قبل موظفينا الموثوقين الذين يوقعون التزامات سرية صارمة.
                </p>
            </article>

            <!-- =================== شروط الطلب والتوصيل =================== -->
            <article class="policy-section">
                <h2>شروط الطلب والتوصيل</h2>
                
                <h3 style="font-size: 18px; margin-top: 20px; margin-bottom: 12px;">نظام الدفع عند الاستلام (Cash on Delivery)</h3>
                <p>
                    متجر D&amp;A Product يعتمد على نظام <strong>"الدفع عند الاستلام"</strong> كطريقة دفع أساسية:
                </p>
                <ul style="margin: 15px 0; line-height: 1.8;">
                    <li>✅ تقديم الطلب <strong>مجاني تماماً بدون رسوم خفية</strong></li>
                    <li>✅ الدفع يتم <strong>عند استقبال الطلب مباشرة من السائق أو الموصل</strong></li>
                    <li>✅ <strong>لا توجد رسوم توصيل إضافية</strong> (محسوب في السعر النهائي)</li>
                    <li>✅ يمكنك <strong>فحص المنتج قبل الدفع</strong> والتأكد من سلامته</li>
                </ul>

                <h3 style="font-size: 18px; margin-top: 25px; margin-bottom: 12px;">التأكيد والتواصل قبل الشحن</h3>
                <p>
                    <strong>بعد تقديمك الطلب مباشرة، سيتم التواصل معك:</strong>
                </p>
                <ul style="margin: 15px 0; line-height: 1.8;">
                    <li>📱 <strong>عبر الواتساب أو الاتصال الهاتفي</strong> خلال ساعات العمل (من 9 ص إلى 6 م)</li>
                    <li>✔️ <strong>تأكيد الطلب:</strong> نتحقق من توفر المنتج وسلامته قبل الشحن</li>
                    <li>🎯 <strong>تحديد موعد التوصيل:</strong> نحدد معك أنسب موعد للاستقبال</li>
                    <li>🚚 <strong>رقم التتبع:</strong> نزودك برقم تتبع الشحنة لتراقب وصول طلبك</li>
                </ul>

                <h3 style="font-size: 18px; margin-top: 25px; margin-bottom: 12px;">أهمية إدخال المعلومات الصحيحة</h3>
                <p style="color: #d32f2f; background: #ffebee; padding: 15px; border-right: 4px solid #f44336; border-radius: 4px;">
                    ⚠️ <strong>تحذير مهم:</strong> يرجى التأكد من أن المعلومات التي تدخلها صحيحة وكاملة:
                </p>
                <ul style="margin: 15px 0; line-height: 1.8;">
                    <li>❌ <strong>رقم هاتف خاطئ:</strong> قد لا نتمكن من التواصل معك لتأكيد الطلب</li>
                    <li>❌ <strong>عنوان غير واضح:</strong> قد تتأخر عملية التوصيل أو قد لا تصل الشحنة</li>
                    <li>❌ <strong>اسم مختلف:</strong> قد يرفض الموصل الشحنة عند الاستلام</li>
                </ul>
                <p style="margin-top: 15px;">
                    <strong>🎯 ضمان سريع:</strong> إدخالك للمعلومات الصحيحة يضمن وصول طلبك إلى يديك في أسرع وقت ممكن!
                </p>
            </article>

            <!-- =================== سياسة الاستبدال والاسترجاع =================== -->
            <article class="policy-section">
                <h2>سياسة الاستبدال والاسترجاع</h2>
                
                <h3 style="font-size: 18px; margin-top: 20px; margin-bottom: 12px;">حقوق الزبون عند الاستلام</h3>
                <p>
                    <strong>لديك الحق الكامل في فحص المنتج عند الاستلام قبل إتمام عملية الدفع:</strong>
                </p>
                <div style="background: #f9f9f9; padding: 20px; border-radius: 6px; margin: 20px 0; border-left: 4px solid #4caf50;">
                    <h4 style="margin-top: 0; color: #2c5f2d;">✅ فحص المنتج:</h4>
                    <ul style="margin: 12px 0; line-height: 1.8;">
                        <li>التأكد من أن <strong>البرطمان سليم وخالٍ من التشقق أو التسرب</strong></li>
                        <li>التحقق من أن <strong>العسل بالكمية المطلوبة والنقاء المتوقع</strong></li>
                        <li>مراجعة <strong>الملصق والتاريخ وسلامة التعبئة</strong></li>
                    </ul>
                </div>

                <h3 style="font-size: 18px; margin-top: 25px; margin-bottom: 12px;">في حالة وجود عيب أو تلف</h3>
                <p>
                    <strong>إذا وجدت أي عيب أو تلف في المنتج، يحق لك:</strong>
                </p>
                <ul style="margin: 15px 0; line-height: 1.8;">
                    <li>🚫 <strong>رفض الاستلام:</strong> يمكنك رفض الشحنة فوراً دون الدفع</li>
                    <li>🔄 <strong>طلب الاستبدال:</strong> سنرسل لك منتج جديد بدل التالف فوراً</li>
                    <li>💰 <strong>استرجاع المبلغ:</strong> إذا أردت الاسترجاع بدلاً من الاستبدال، سنرد لك المبلغ كاملاً</li>
                </ul>

                <h3 style="font-size: 18px; margin-top: 25px; margin-bottom: 12px;">كيفية التواصل في حالة المشكلة</h3>
                <p>
                    إذا واجهت أي مشكلة مع الطلب أو المنتج، يرجى التواصل معنا فوراً:
                </p>
                <ul style="margin: 15px 0; line-height: 1.8;">
                    <li>📱 <strong>واتساب:</strong> <a href="https://wa.me/<?= e($waNum) ?>?text=لدي%20مشكلة%20مع%20طلبي" target="_blank" rel="noopener" style="color: #4caf50; text-decoration: none;">تواصل معنا عبر واتساب</a></li>
                    <li>☎️ <strong>اتصال مباشر:</strong> +<?= e($waNum) ?></li>
                    <li>💬 <strong>رسالة:</strong> سنرد عليك خلال 24 ساعة بأقصى سرعة</li>
                </ul>

                <p style="background: #e3f2fd; padding: 15px; border-right: 4px solid #2196f3; border-radius: 4px; margin-top: 20px;">
                    <strong>💡 التزامنا:</strong> رضاك هو أولويتنا. لن نترك أي مشكلة دون حل، وسنعمل على حلها بسرعة واحترافية.
                </p>
            </article>

            <!-- =================== حقوق إضافية =================== -->
            <article class="policy-section">
                <h2>حقوقك الإضافية</h2>
                <ul style="margin: 20px 0; line-height: 1.8;">
                    <li><strong>حق الوصول:</strong> يمكنك طلب نسخة من بيانات عملك معنا في أي وقت</li>
                    <li><strong>حق الحذف:</strong> يمكنك طلب حذف بيانات شخصية معينة من قاعدتنا</li>
                    <li><strong>حق الاعتراض:</strong> يمكنك الاعتراض على أي استخدام غير موافق عليه لبيانات عملك</li>
                    <li><strong>عدم المراسلة الإعلانية:</strong> إذا لم تردنا أن نتواصل معك، يمكنك إبلاغنا بذلك فوراً</li>
                </ul>
            </article>

            <!-- =================== التغييرات على السياسة =================== -->
            <article class="policy-section">
                <h2>التغييرات على هذه السياسة</h2>
                <p>
                    قد نقوم بتحديث سياسة الخصوصية من وقت لآخر لتعكس التغييرات في ممارساتنا أو لأسباب قانونية أخرى. 
                    سيتم إعلام جميع العملاء بأي تغييرات جوهرية عبر بريد إلكتروني أو إشعار على الموقع.
                </p>
                <p style="color: #666; font-size: 14px;">
                    <strong>آخر تحديث:</strong> 29 مايو 2026
                </p>
            </article>

            <!-- =================== التواصل =================== -->
            <article class="policy-section">
                <h2>هل لديك أسئلة؟</h2>
                <p>
                    إذا كان لديك أي استفسار حول سياسة الخصوصية أو شروط الخدمة، يرجى التواصل معنا:
                </p>
                <div style="background: #f0f4c3; padding: 20px; border-radius: 6px; margin: 20px 0; border-right: 4px solid #fbc02d;">
                    <p style="margin: 0;">
                        <strong>📞 للتواصل المباشر:</strong><br>
                        واتساب: <a href="https://wa.me/<?= e($waNum) ?>" target="_blank" rel="noopener" style="color: #4caf50; text-decoration: none;">+<?= e($waNum) ?></a><br>
                        متجرنا على: <a href="index.php" style="color: #f57c00; text-decoration: none;">D&amp;A Product</a>
                    </p>
                </div>
                <p style="text-align: center; color: #666; font-size: 14px; margin-top: 30px;">
                    شكراً لثقتك بنا ولاختيارك عسل D&amp;A – الجودة والأمان والشفافية هي ضماننا لك! 🍯
                </p>
            </article>

        </div>
    </section>

    <footer class="site-footer" id="footer">
        <div class="container footer-grid">
            <div class="footer-col">
                <span class="logo-text">D&amp;A Product</span>
                <p><?= e(SITE_TAGLINE) ?></p>
            </div>
            <div class="footer-col">
                <h4>روابط سريعة</h4>
                <a href="index.php#products">منتجاتنا</a>
                <a href="index.php#story">قصتنا</a>
                <a href="index.php#order">اطلب الآن</a>
                <a href="index.php#tips">نصائح العسل</a>
                <a href="privacy-policy.php">السياسة والشروط</a>
            </div>
            <div class="footer-col">
                <h4>تواصل معنا</h4>
                <a href="https://wa.me/<?= e($waNum) ?>?text=<?= $waMsg ?>" target="_blank" rel="noopener">واتساب</a>
                <a href="<?= e(runtime('CONTACT_INSTAGRAM', CONTACT_INSTAGRAM)) ?>" target="_blank" rel="noopener">إنستغرام</a>
                <a href="<?= e(runtime('CONTACT_TIKTOK', CONTACT_TIKTOK)) ?>" target="_blank" rel="noopener">تيك توك</a>
            </div>
            <div class="footer-col">
                <h4>النشرة البريدية</h4>
                <form id="newsletterForm" class="newsletter-form">
                    <input type="email" name="email" placeholder="بريدك الإلكتروني" required>
                    <button type="submit">اشترك</button>
                </form>
            </div>
        </div>
        <p class="footer-copy">&copy; <?= date('Y') ?> D&amp;A Product – جميع الحقوق محفوظة</p>
    </footer>

    <a href="https://wa.me/<?= e($waNum) ?>?text=<?= $waMsg ?>" class="whatsapp-float" target="_blank" rel="noopener" aria-label="واتساب"><svg viewBox="0 0 24 24" width="28" fill="#fff"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg></a>

    <style>
        .policy-section {
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 1px solid #eee;
        }
        .policy-section:last-child {
            border-bottom: none;
        }
        .policy-section h2 {
            font-size: 24px;
            color: #2c5f2d;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #4caf50;
        }
        .policy-section h3 {
            font-size: 18px;
            color: #333;
            margin-top: 20px;
            margin-bottom: 12px;
        }
        .policy-section p {
            font-size: 15px;
            line-height: 1.7;
            color: #555;
            margin-bottom: 15px;
        }
        .policy-section ul {
            padding-right: 20px;
        }
        .policy-section li {
            margin-bottom: 10px;
            line-height: 1.7;
            color: #555;
        }
        .policy-section a {
            color: #4caf50;
            text-decoration: none;
            font-weight: 500;
        }
        .policy-section a:hover {
            text-decoration: underline;
        }
    </style>

    <script>window.DA_CSRF = <?= json_encode(csrf_token(), JSON_UNESCAPED_UNICODE) ?>;</script>
    <script src="<?= asset('assets/js/main.min.js') ?>" defer></script>
</body>
</html>
