<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Learn together. Grow together.';
include __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <div class="hero-glow"></div>
    <div class="container hero-grid">
        <div class="hero-copy">
            <span class="eyebrow"><span class="eyebrow-dot"></span> Your campus is full of potential</span>
            <h1>Trade what you know.<br><span>Learn what you love.</span></h1>
            <p class="hero-lead">A community where students teach students. Share your skills, find your next mentor, and make learning a two-way street.</p>
            <div class="hero-actions">
                <a class="button" href="<?= current_user() ? 'discover.php' : 'register.php' ?>">Find your people <span aria-hidden="true">↗</span></a>
                <a class="text-link" href="#how-it-works">See how it works <span aria-hidden="true">↓</span></a>
            </div>
            <div class="hero-proof">
                <div class="avatar-stack" aria-hidden="true"><span>A</span><span>M</span><span>J</span><span>+</span></div>
                <p><strong>A better way to learn</strong><br>Built around your campus community</p>
            </div>
        </div>
        <div class="hero-visual" aria-label="Example student skill exchange">
            <div class="visual-orbit orbit-one"></div><div class="visual-orbit orbit-two"></div>
            <div class="floating-note note-top"><span class="note-icon note-purple">✦</span><span><strong>Skill match found</strong><small>Just for you</small></span><span class="note-check">✓</span></div>
            <div class="exchange-card">
                <div class="exchange-card-top"><span class="live-dot"></span> A little knowledge goes a long way</div>
                <div class="person-row">
                    <div class="person-avatar avatar-indigo">AM</div>
                    <div class="person-copy"><strong>Alex Morgan</strong><small>Design student · Year 2</small></div>
                    <span class="person-tag">TEACHES</span>
                </div>
                <div class="skill-line"><span>✦</span><strong>Figma & UI design</strong><small>↗</small></div>
                <div class="exchange-connector"><span>↕</span><small>SKILL EXCHANGE</small></div>
                <div class="person-row">
                    <div class="person-avatar avatar-pink">SK</div>
                    <div class="person-copy"><strong>Sam Kim</strong><small>Computer science · Year 3</small></div>
                    <span class="person-tag tag-wants">WANTS TO LEARN</span>
                </div>
                <div class="skill-line skill-line-light"><span>✦</span><strong>Presentation skills</strong><small>↗</small></div>
            </div>
            <div class="floating-note note-bottom"><span class="success-ring">✓</span><span><strong>Knowledge shared</strong><small>Everyone wins</small></span></div>
            <div class="visual-spark spark-one">✦</div><div class="visual-spark spark-two">✧</div>
        </div>
    </div>
    <div class="container hero-bottom"><span>MADE FOR CURIOUS MINDS</span><span class="hero-bottom-line"></span><span>LEARN FROM EACH OTHER</span></div>
</section>
<section class="section how-section" id="how-it-works">
    <div class="container">
        <div class="section-heading centered">
            <span class="eyebrow">A simple idea, a big difference</span>
            <h2>Learning is better <span>together.</span></h2>
            <p>Turn the skills you already have into the skills you want.</p>
        </div>
        <div class="steps-grid">
            <article class="step-card"><span class="step-number">01</span><div class="step-icon icon-lavender">✳</div><h3>Share your skills</h3><p>Tell the community what you’re great at and happy to teach.</p><span class="step-arrow">↗</span></article>
            <article class="step-card"><span class="step-number">02</span><div class="step-icon icon-peach">⌕</div><h3>Find your match</h3><p>Discover students with the skills you’re curious to learn.</p><span class="step-arrow">↗</span></article>
            <article class="step-card"><span class="step-number">03</span><div class="step-icon icon-mint">↗</div><h3>Exchange knowledge</h3><p>Connect, share what you know, and grow side by side.</p><span class="step-arrow">↗</span></article>
        </div>
    </div>
</section>
<section class="cta-section">
    <div class="container cta-panel"><div><span class="eyebrow eyebrow-light">Your next skill is closer than you think</span><h2>Ready to make a connection?</h2><p>Join your campus community and start sharing what you know.</p></div><a class="button button-white" href="<?= current_user() ? 'discover.php' : 'register.php' ?>">Get started <span aria-hidden="true">↗</span></a><span class="cta-decoration">✳</span></div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
