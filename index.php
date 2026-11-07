<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['contact_form'] ?? '') === '1') {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');

    $fields = ['name' => '', 'email' => '', 'message' => ''];
    foreach ($fields as $field => $_) {
        $value = $_POST[$field] ?? '';
        $fields[$field] = is_string($value) ? trim($value) : '';
    }

    $errors = [];
    if ($fields['name'] === '') {
        $errors['name'] = 'Please enter your name.';
    } elseif (strlen($fields['name']) > 100) {
        $errors['name'] = 'Your name must be 100 characters or fewer.';
    }
    if ($fields['email'] === '') {
        $errors['email'] = 'Please enter your email address.';
    } elseif (strlen($fields['email']) > 254 || !filter_var($fields['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }
    if ($fields['message'] === '') {
        $errors['message'] = 'Please enter a message.';
    } elseif (strlen($fields['message']) > 3000) {
        $errors['message'] = 'Your message must be 3000 characters or fewer.';
    }

    $respond = static function (int $status, array $payload): void {
        http_response_code($status);
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        exit;
    };

    if ($errors !== []) {
        $respond(422, ['success' => false, 'feedback' => 'Please review the highlighted details and try again.', 'errors' => $errors]);
    }

    // Quietly discard submissions caught by the Formspree-compatible honeypot.
    $honeypot = $_POST['_gotcha'] ?? '';
    if (!is_string($honeypot) || trim($honeypot) !== '') {
        $respond(200, ['success' => true, 'feedback' => 'Thanks — your message was sent successfully.', 'errors' => []]);
    }

    $formspreeEndpoint = 'https://formspree.io/f/xljjkjlw';
    $submission = [
        'name' => $fields['name'],
        'email' => $fields['email'],
        '_replyto' => $fields['email'],
        'message' => $fields['message'],
        '_subject' => 'New portfolio contact message',
    ];

    if (!function_exists('curl_init')) {
        error_log('Portfolio contact error: PHP cURL is not available.');
        $respond(502, ['success' => false, 'feedback' => 'The message service is temporarily unavailable. Please email me directly.', 'errors' => []]);
    }

    $curl = curl_init($formspreeEndpoint);
    curl_setopt_array($curl, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($submission, '', '&', PHP_QUERY_RFC3986),
        CURLOPT_HTTPHEADER => ['Accept: application/json', 'Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_TIMEOUT => 20,
    ]);
    $providerResponse = curl_exec($curl);
    $providerStatus = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $providerError = curl_error($curl);
    curl_close($curl);

    if ($providerResponse !== false && $providerStatus >= 200 && $providerStatus < 300) {
        $respond(200, ['success' => true, 'feedback' => 'Thanks — your message was sent successfully.', 'errors' => []]);
    }

    error_log('Portfolio contact delivery failed. HTTP status: ' . $providerStatus . ($providerError !== '' ? '; ' . $providerError : ''));
    $respond(502, ['success' => false, 'feedback' => 'Your message could not be sent right now. Please try again in a moment or email me directly.', 'errors' => []]);
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Lawrence Marcos is an Information Technology student interested in networking. Explore his projects, including a multi-branch network design and a PHP warehouse analyzer.">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Lawrence Marcos — Portfolio">
    <meta property="og:description" content="IT student interested in networking. Projects, background and contact details.">
    <meta name="twitter:card" content="summary">
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' rx='22' fill='%23191919'/%3E%3Ctext x='50' y='66' font-size='43' font-style='italic' letter-spacing='-6' text-anchor='middle' fill='%23fafaf8' font-family='Georgia,serif'%3ELM%3C/text%3E%3C/svg%3E">
    <meta name="theme-color" id="theme-color" content="#fafaf8">
    <title>Lawrence Marcos — Portfolio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=Instrument+Serif:ital@0;1&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script>
        /* Apply the saved/system theme before first paint to avoid a light-mode flash. */
        (function() {
            var theme = null;
            try {
                theme = localStorage.getItem('portfolio-theme');
            } catch (e) {}
            if (theme !== 'dark' && theme !== 'light') {
                theme = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            document.documentElement.dataset.theme = theme;
        })();
    </script>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <a class="skip-link" href="#main">Skip to content</a>
    <header class="site-header">
        <nav class="nav-shell" aria-label="Main navigation">
            <a class="brand" href="#home" aria-label="Lawrence Marcos, home"><span class="brand-mark" aria-hidden="true">LM</span><span>Lawrence Marcos</span></a>
            <div class="nav-links"><a href="#about">About</a><a href="#projects">Projects</a><a href="#contact">Contact <span aria-hidden="true">↗</span></a><button class="theme-toggle" id="theme-toggle" type="button" aria-label="Switch to dark mode"><span aria-hidden="true">◐</span></button></div>
        </nav>
    </header>

    <main id="main">
        <section class="hero page-wrap" id="home" aria-labelledby="hero-title">
            <div class="hero-caption mono"><span>A PERSONAL SPACE ON THE INTERNET</span></div>
            <div class="hero-main">
                <div class="hero-copy">
                    <h1 id="hero-title">Hello, I’m<br>Lawrence.</h1>
                    <p class="hero-intro">A little about me, the things I make,<br>and what I’m interested about.</p>
                    <a class="pill-button" href="#projects">Explore my work <span aria-hidden="true">↗</span></a>
                </div>
                <aside class="identity-card" aria-label="Portrait of Lawrence Marcos">
                    <img class="portrait-image" src="assets/lawrence-portrait.jpg" alt="Lawrence Marcos">
                </aside>
            </div>
            <div class="hero-footer mono"><a href="#about">SCROLL TO DISCOVER <span aria-hidden="true">↓</span></a></div>
        </section>

        <section class="about-section page-wrap" id="about" aria-labelledby="about-title">
            <p class="section-index mono">[ 01 — ABOUT ]</p>
            <div class="about-content">
                <h2 id="about-title">More than a collection of work.<br>A glimpse into how I <em>see things.</em></h2>
                <p class="about-lead">I’m Lawrence Marcos, an Information Technology student who likes exploring how technology works behind the scenes—and turning what I learn into something tangible.</p>
                <div class="about-columns">
                    <p>Networking is one area I’m especially interested in. I enjoy learning how devices communicate and how a well-planned network supports the people and systems that depend on it.</p>
                </div>
                <div class="about-facts" aria-label="A few details about Lawrence">
                    <div class="about-fact"><span class="mono">EDUCATION</span><strong>BS in Information Technology</strong><span>Pamantasan ng Lungsod ng Valenzuela</span></div>
                    <div class="about-fact"><span class="mono">CURRENT INTEREST</span><strong>Networking</strong><span>How devices and systems connect</span></div>
                </div>
                <div class="about-signoff"><span class="signature">Lawrence Marcos</span><span class="mono">STILL LEARNING. STILL BECOMING.</span></div>
            </div>
        </section>

        <section class="work-section" id="projects" aria-labelledby="projects-title">
            <div class="page-wrap">
                <p class="section-index mono">[ 02 — SELECTED WORK ]</p>
                <div class="work-heading">
                    <h2 id="projects-title">A few ideas,<br><em>made tangible.</em></h2>
                    <p><span class="mono">COURSE PROJECTS, BUILT COLLABORATIVELY</span><br>A collection of projects, and ideas I’ve enjoyed bringing to life</p>
                </div>
                <div class="project-list">
                    <article class="project-row">
                        <div class="project-copy">
                            <p class="project-kicker mono">01 / TEAM PROJECT — CISCO PACKET TRACER</p>
                            <h3>NOVO Branch Network Infrastructure</h3>
                            <p class="project-description">A proposed network design for NOVO’s Malinta, Karuhatan, and Paso de Blas branches.<br><br>The plan covers VLAN segmentation, IP addressing, DHCP, inter-branch routing, and the physical layouts for network equipment and cabling.</p>
                            <ul class="project-tags" aria-label="Project topics">
                                <li>THREE BRANCHES</li>
                                <li>VLAN DESIGN</li>
                                <li>IP ADDRESSING</li>
                                <li>DHCP</li>
                                <li>ROUTING</li>
                            </ul>
                        </div>
                        <div class="project-preview preview-network"><button class="network-image-trigger project-image-trigger" type="button" data-image-title="NOVO network infrastructure / three branches" aria-label="Enlarge the complete NOVO network topology"><img class="network-screenshot" src="assets/novo-three-branch-topology.png" alt="Complete Cisco Packet Tracer topology overview connecting NOVO’s Malinta, Karuhatan, and Paso de Blas branches"></button><span class="preview-note mono">MALINTA / KARUHATAN / PASO DE BLAS · CLICK TO ENLARGE</span></div>
                    </article>
                    <article class="project-row">
                        <div class="project-copy">
                            <p class="project-kicker mono">02 / WAREHOUSE TRACKING</p>
                            <h3>Simple Warehouse Performance Tracker Analyzer</h3>
                            <p class="project-description">A simple PHP tool for reviewing daily warehouse sales across five products.<br><br>It classifies daily performance, calculates weekly sales averages, and tracks available stock with a restock threshold.</p>
                            <ul class="project-tags" aria-label="Project topics">
                                <li>PHP</li>
                                <li>DAILY SALES</li>
                                <li>WEEKLY AVERAGES</li>
                                <li>STOCK TRACKING</li>
                            </ul>
                        </div>
                        <div class="project-preview preview-two">
                            <div class="warehouse-gallery" id="warehouse-gallery" role="group" aria-roledescription="carousel" aria-label="Warehouse tracker screenshots">
                                <button class="gallery-arrow" id="warehouse-previous" type="button" aria-label="Previous project image" hidden>←</button>
                                <div class="warehouse-gallery-stage">
                                    <figure class="warehouse-gallery-item" data-gallery-slide role="group" aria-roledescription="slide" aria-label="1 of 3">
                                        <button class="project-image-trigger gallery-image-trigger" type="button" data-image-title="Warehouse tracker / unanalyzed daily view" aria-label="Enlarge the uncolored daily warehouse screenshot"><img class="project-screenshot" src="assets/warehouse-tracker-daily.png" alt="Unanalyzed daily warehouse performance table with product stock, sales selectors, and no classification colors"></button>
                                        <figcaption class="mono">UNANALYZED DAILY VIEW <span>1 / 3</span></figcaption>
                                    </figure>
                                    <figure class="warehouse-gallery-item" data-gallery-slide role="group" aria-roledescription="slide" aria-label="2 of 3" hidden>
                                        <button class="project-image-trigger gallery-image-trigger" type="button" data-image-title="Warehouse tracker / daily performance results" aria-label="Enlarge daily warehouse performance results"><img class="project-screenshot" src="assets/warehouse-tracker.png" alt="Analyzed Monday warehouse performance with color-coded product classifications"></button>
                                        <figcaption class="mono">DAILY PERFORMANCE <span>2 / 3</span></figcaption>
                                    </figure>
                                    <figure class="warehouse-gallery-item" data-gallery-slide role="group" aria-roledescription="slide" aria-label="3 of 3" hidden>
                                        <button class="project-image-trigger gallery-image-trigger" type="button" data-image-title="Warehouse tracker / weekly performance" aria-label="Enlarge weekly warehouse performance screenshot"><img class="project-screenshot" src="assets/warehouse-tracker-weekly.png" alt="Weekly warehouse performance table with average units sold and classifications"></button>
                                        <figcaption class="mono">WEEKLY PERFORMANCE <span>3 / 3</span></figcaption>
                                    </figure>
                                </div>
                                <button class="gallery-arrow" id="warehouse-next" type="button" aria-label="Next project image">→</button>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section class="contact-section page-wrap" id="contact" aria-labelledby="contact-title">
            <p class="section-index mono">[ 03 — SAY HELLO ]</p>
            <div class="contact-card">
                <div class="contact-details">
                    <div>
                        <h2 id="contact-title">Let’s talk<br>and work together.</h2>
                    </div>
                    <div class="contact-links">
                        <div class="contact-info-list" aria-label="Contact information">
                            <div class="contact-info-item">
                                <span class="contact-info-icon" aria-hidden="true">✉</span>
                                <div><span class="contact-info-label mono">EMAIL</span><a id="contact-email" href="mailto:lawrencemarcos8@gmail.com">lawrencemarcos8@gmail.com</a></div>
                                <button class="copy-email-button" id="copy-email" type="button" aria-label="Copy email address" title="Copy email address">
                                    <svg class="copy-email-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                        <rect x="8" y="8" width="12" height="13" rx="2"></rect>
                                        <path d="M16 8V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h3"></path>
                                    </svg>
                                </button>
                            </div>
                            <div class="contact-info-item">
                                <span class="contact-info-icon" aria-hidden="true">⌖</span>
                                <div><span class="contact-info-label mono">LOCATION</span><span>Valenzuela City, Philippines</span></div>
                            </div>
                            <div class="contact-info-item">
                                <span class="contact-info-icon" aria-hidden="true">☎</span>
                                <div><span class="contact-info-label mono">CONTACT NUMBER</span><span>09620431764</span></div>
                            </div>
                        </div>
                        <span class="copy-email-status" id="copy-email-status" aria-live="polite"></span>
                        <a class="contact-github mono" href="https://github.com/LawrenceMarcos" target="_blank" rel="noopener noreferrer">GITHUB <span aria-hidden="true">↗</span></a>
                    </div>
                </div>
                <form class="contact-form" id="contact-form" method="post" action="https://formspree.io/f/xljjkjlw" data-validation-endpoint="index.php">
                    <input type="hidden" name="contact_form" value="1">
                    <input type="hidden" name="_subject" value="New portfolio contact message">
                    <input type="hidden" id="contact-reply-to" name="_replyto" value="">
                    <p class="form-feedback" id="contact-feedback" role="status" aria-live="polite" hidden></p>
                    <ul class="form-errors" id="contact-error-summary" aria-label="Form errors" hidden></ul>
                    <div class="contact-field">
                        <label for="contact-name">Your name</label>
                        <input id="contact-name" name="name" type="text" autocomplete="name" maxlength="100" placeholder="Type your name here" required aria-describedby="contact-name-error">
                        <span class="field-error" id="contact-name-error" data-field-error="name" hidden></span>
                    </div>
                    <div class="contact-field">
                        <label for="contact-email-input">Your email</label>
                        <input id="contact-email-input" name="email" type="email" autocomplete="email" maxlength="254" placeholder="Type your email address here" required aria-describedby="contact-email-error">
                        <span class="field-error" id="contact-email-error" data-field-error="email" hidden></span>
                    </div>
                    <div class="contact-field">
                        <label for="contact-message">Message</label>
                        <textarea id="contact-message" name="message" rows="4" maxlength="3000" placeholder="Write your message here" required aria-describedby="contact-message-error"></textarea>
                        <span class="field-error" id="contact-message-error" data-field-error="message" hidden></span>
                    </div>
                    <p class="privacy-note">Your message will be processed by Formspree and forwarded to my email. Please do not include sensitive personal information.</p>
                    <div class="website-field" aria-hidden="true"><label for="_gotcha">Leave this field empty</label><input id="_gotcha" name="_gotcha" type="text" tabindex="-1" autocomplete="off"></div>
                    <button class="pill-button" type="submit"><span class="submit-label">Send message</span><span aria-hidden="true">↗</span></button>
                </form>
            </div>
        </section>
    </main>

    <footer class="site-footer page-wrap">
        <div class="footer-top"><a class="footer-name" href="#home">Lawrence Marcos</a>
            <nav aria-label="Footer navigation"><a href="#about">About</a><a href="#projects">Projects</a><a href="#contact">Contact</a></nav><a class="back-top" href="#home" aria-label="Back to top">↑</a>
        </div>
        <div class="footer-bottom mono"><span>© <?php echo date('Y'); ?> LAWRENCE MARCOS. A PERSONAL PORTFOLIO.</span><span>THANKS FOR SPENDING A LITTLE TIME HERE.</span></div>
    </footer>
    <dialog class="image-dialog" id="network-image-dialog" aria-labelledby="network-dialog-title">
        <div class="lightbox-header">
            <p id="network-dialog-title" class="mono">PROJECT IMAGE PREVIEW</p><button class="lightbox-close" id="close-network-image" type="button">Close <span aria-hidden="true">×</span></button>
        </div>
        <div class="lightbox-viewer">
            <button class="lightbox-nav" id="lightbox-previous" type="button" aria-label="Previous warehouse screenshot" hidden>←</button>
            <img class="lightbox-image" src="assets/novo-three-branch-topology.png" alt="">
            <button class="lightbox-nav" id="lightbox-next" type="button" aria-label="Next warehouse screenshot" hidden>→</button>
        </div>
    </dialog>
    <button class="back-to-top-button" id="back-to-top" type="button" aria-label="Back to top" title="Back to top" hidden>↑</button>
    <script>
        (() => {
            const root = document.documentElement;
            const toggle = document.getElementById('theme-toggle');
            const themeColor = document.getElementById('theme-color');
            const colors = {
                light: '#fafaf8',
                dark: '#191917'
            };

            function setTheme(theme) {
                root.dataset.theme = theme;
                toggle.setAttribute('aria-label', theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
                toggle.querySelector('span').textContent = theme === 'dark' ? '☀' : '◐';
                if (themeColor) themeColor.setAttribute('content', colors[theme]);
            }

            setTheme(root.dataset.theme === 'dark' ? 'dark' : 'light');

            toggle.addEventListener('click', () => {
                const nextTheme = root.dataset.theme === 'dark' ? 'light' : 'dark';
                try {
                    localStorage.setItem('portfolio-theme', nextTheme);
                } catch (e) {}
                setTheme(nextTheme);
            });

            const copyEmailButton = document.getElementById('copy-email');
            const emailAddress = document.getElementById('contact-email').textContent.trim();
            const copyEmailStatus = document.getElementById('copy-email-status');
            let copyStatusTimer;
            copyEmailButton.addEventListener('click', async () => {
                try {
                    await navigator.clipboard.writeText(emailAddress);
                    copyEmailStatus.textContent = 'Email copied';
                    copyEmailButton.setAttribute('aria-label', 'Email copied');
                    copyEmailButton.setAttribute('title', 'Email copied');
                    copyEmailButton.classList.add('is-copied');
                } catch (error) {
                    copyEmailStatus.textContent = 'Copy unavailable. Select the email address to copy it.';
                }
                window.clearTimeout(copyStatusTimer);
                copyStatusTimer = window.setTimeout(() => {
                    copyEmailStatus.textContent = '';
                    copyEmailButton.setAttribute('aria-label', 'Copy email address');
                    copyEmailButton.setAttribute('title', 'Copy email address');
                    copyEmailButton.classList.remove('is-copied');
                }, 2000);
            });

            const backToTopButton = document.getElementById('back-to-top');
            let isFooterVisible = false;
            const updateBackToTopVisibility = () => {
                backToTopButton.hidden = window.scrollY < 500 || isFooterVisible;
            };
            window.addEventListener('scroll', updateBackToTopVisibility, {
                passive: true
            });
            backToTopButton.addEventListener('click', () => window.scrollTo({
                top: 0,
                behavior: 'smooth'
            }));
            updateBackToTopVisibility();

            const networkDialog = document.getElementById('network-image-dialog');
            const imageTriggers = document.querySelectorAll('.project-image-trigger');
            const closeNetworkImage = document.getElementById('close-network-image');
            const dialogImage = networkDialog.querySelector('.lightbox-image');
            const dialogTitle = document.getElementById('network-dialog-title');
            const warehouseGallery = document.getElementById('warehouse-gallery');
            const slides = warehouseGallery ? Array.from(warehouseGallery.querySelectorAll('[data-gallery-slide]')) : [];
            const previousButton = document.getElementById('warehouse-previous');
            const nextButton = document.getElementById('warehouse-next');
            const lightboxPrevious = document.getElementById('lightbox-previous');
            const lightboxNext = document.getElementById('lightbox-next');
            let activeSlide = 0;
            let expandedSlide = -1;

            function showGallerySlide(index) {
                if (!slides.length) return;
                activeSlide = Math.max(0, Math.min(index, slides.length - 1));
                slides.forEach((slide, slideIndex) => {
                    slide.hidden = slideIndex !== activeSlide;
                });
                previousButton.hidden = activeSlide === 0;
                nextButton.hidden = activeSlide === slides.length - 1;
            }

            function showExpandedSlide(index) {
                expandedSlide = Math.max(0, Math.min(index, slides.length - 1));
                const trigger = slides[expandedSlide].querySelector('.project-image-trigger');
                const image = trigger.querySelector('img');
                dialogImage.src = image.currentSrc || image.src;
                dialogImage.alt = image.alt;
                dialogTitle.textContent = trigger.dataset.imageTitle || 'Warehouse tracker screenshot';
                lightboxPrevious.hidden = expandedSlide === 0;
                lightboxNext.hidden = expandedSlide === slides.length - 1;
                showGallerySlide(expandedSlide);
            }

            imageTriggers.forEach((trigger) => {
                trigger.addEventListener('click', () => {
                    const slideIndex = slides.indexOf(trigger.closest('[data-gallery-slide]'));
                    if (slideIndex !== -1) {
                        showExpandedSlide(slideIndex);
                    } else {
                        const image = trigger.querySelector('img');
                        expandedSlide = -1;
                        dialogImage.src = image.currentSrc || image.src;
                        dialogImage.alt = image.alt;
                        dialogTitle.textContent = trigger.dataset.imageTitle || 'Project image preview';
                        lightboxPrevious.hidden = true;
                        lightboxNext.hidden = true;
                    }
                    networkDialog.showModal();
                });
            });
            lightboxPrevious.addEventListener('click', () => showExpandedSlide(expandedSlide - 1));
            lightboxNext.addEventListener('click', () => showExpandedSlide(expandedSlide + 1));
            closeNetworkImage.addEventListener('click', () => networkDialog.close());
            networkDialog.addEventListener('click', (event) => {
                if (event.target === networkDialog) networkDialog.close();
            });

            if (warehouseGallery) {
                previousButton.addEventListener('click', () => showGallerySlide(activeSlide - 1));
                nextButton.addEventListener('click', () => showGallerySlide(activeSlide + 1));
                showGallerySlide(activeSlide);
            }

            const imagesToLoad = document.querySelectorAll('.network-screenshot, .project-screenshot, .lightbox-image');
            imagesToLoad.forEach((image) => {
                image.loading = 'lazy';
                image.addEventListener('load', () => {
                    image.classList.add('is-loaded');
                    image.closest('.project-preview')?.classList.add('image-ready');
                }, {
                    once: true
                });
                image.addEventListener('error', () => {
                    image.closest('.project-preview')?.classList.add('image-failed');
                }, {
                    once: true
                });
                if (image.complete && image.naturalWidth > 0) {
                    image.classList.add('is-loaded');
                    image.closest('.project-preview')?.classList.add('image-ready');
                }
            });

            const contactForm = document.getElementById('contact-form');
            const contactFeedback = document.getElementById('contact-feedback');
            const contactErrorSummary = document.getElementById('contact-error-summary');
            let contactFeedbackTimer;
            const hideSuccessfulContactFeedback = () => {
                window.clearTimeout(contactFeedbackTimer);
                contactFeedbackTimer = window.setTimeout(() => {
                    contactFeedback.hidden = true;
                }, 5000);
            };
            if (contactFeedback && contactFeedback.classList.contains('success') && !contactFeedback.hidden) {
                hideSuccessfulContactFeedback();
            }
            if (contactForm && contactFeedback && contactErrorSummary && window.fetch && window.FormData) {
                const contactFields = {
                    name: document.getElementById('contact-name'),
                    email: document.getElementById('contact-email-input'),
                    message: document.getElementById('contact-message')
                };
                const submitButton = contactForm.querySelector('button[type="submit"]');
                const submitLabel = submitButton.querySelector('.submit-label');
                const defaultSubmitLabel = submitLabel.textContent;

                contactForm.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    submitButton.disabled = true;
                    submitLabel.textContent = 'Sending…';
                    window.clearTimeout(contactFeedbackTimer);
                    contactFeedback.hidden = true;
                    contactErrorSummary.hidden = true;

                    Object.entries(contactFields).forEach(([name, field]) => {
                        field.removeAttribute('aria-invalid');
                        const error = contactForm.querySelector(`[data-field-error="${name}"]`);
                        error.textContent = '';
                        error.hidden = true;
                    });

                    try {
                        const response = await fetch(contactForm.dataset.validationEndpoint, {
                            method: 'POST',
                            body: new FormData(contactForm),
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin'
                        });
                        const result = await response.json().catch(() => ({}));
                        const fieldErrors = result.errors && typeof result.errors === 'object' ? result.errors : {};
                        const isSuccess = response.ok && result.success === true;
                        contactFeedback.textContent = result.feedback || (isSuccess ?
                            'Thanks — your message was sent successfully.' :
                            'Your message could not be sent. Please try again in a moment or email me directly.');
                        contactFeedback.classList.toggle('error', !isSuccess);
                        contactFeedback.classList.toggle('success', isSuccess);
                        contactFeedback.setAttribute('role', isSuccess ? 'status' : 'alert');
                        contactFeedback.hidden = false;
                        if (isSuccess) hideSuccessfulContactFeedback();

                        const errorEntries = Object.entries(fieldErrors);
                        if (errorEntries.length) {
                            contactErrorSummary.replaceChildren(...errorEntries.map(([name, message]) => {
                                const item = document.createElement('li');
                                item.textContent = message;
                                return item;
                            }));
                            contactErrorSummary.hidden = false;
                            errorEntries.forEach(([name, message]) => {
                                const field = contactFields[name];
                                const error = contactForm.querySelector(`[data-field-error="${name}"]`);
                                if (!field || !error) return;
                                field.setAttribute('aria-invalid', 'true');
                                error.textContent = message;
                                error.hidden = false;
                            });
                        }

                        if (isSuccess) contactForm.reset();
                    } catch (error) {
                        contactFeedback.textContent = 'Could not reach the message service. Check your connection and try again.';
                        contactFeedback.classList.add('error');
                        contactFeedback.classList.remove('success');
                        contactFeedback.setAttribute('role', 'alert');
                        contactFeedback.hidden = false;
                    } finally {
                        submitButton.disabled = false;
                        submitLabel.textContent = defaultSubmitLabel;
                    }
                });
            }

            const header = document.querySelector('.site-header');
            const footer = document.querySelector('.site-footer');
            if (header && footer && 'IntersectionObserver' in window) {
                const footerObserver = new IntersectionObserver(([entry]) => {
                    header.classList.toggle('footer-visible', entry.isIntersecting);
                    isFooterVisible = entry.isIntersecting;
                    updateBackToTopVisibility();
                });
                footerObserver.observe(footer);
            }
        })();
    </script>
</body>

</html>