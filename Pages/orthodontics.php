<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../Logo.png">
    <title>Orthodontics | BrightSmile Dental Clinic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Styling/patient-pages.css">
</head>
<body>
    <div class="app-container">
        <?php include '../Components/Sidebar.php'; ?>
        <main class="main-content">
            <?php include '../Components/Header.php'; ?>
            <section class="page-content patient-page">
                <div class="page-heading page-heading-row">
                    <div>
                        <p class="page-label">YOUR TREATMENT</p>
                        <h1>Orthodontics</h1>
                        <p class="page-description">Track your braces treatment and keep up with your next adjustment.</p>
                    </div>
                    <a class="primary-button" href="book-appointment.php"><span aria-hidden="true">+</span> Request a visit</a>
                </div>

                <div class="orthodontic-overview">
                    <section class="treatment-banner">
                        <span class="eyebrow">ACTIVE TREATMENT · BRACES</span>
                        <h2>Your smile is moving forward</h2>
                        <p>Your treatment is progressing as planned. Keep wearing your elastics as advised and attend your scheduled adjustment visits.</p>
                        <div class="progress-line"><span>Treatment progress</span><strong>65%</strong></div>
                        <div class="progress-track" role="progressbar" aria-label="Treatment progress" aria-valuemin="0" aria-valuemax="100" aria-valuenow="65"><span></span></div>
                    </section>
                    <section class="surface-panel next-visit">
                        <div>
                            <span class="eyebrow">NEXT ADJUSTMENT</span>
                            <strong>23 October 2026</strong>
                            <p>2:00 PM · Dr. Sarah Lim</p>
                        </div>
                        <a class="text-link" href="appointments.php">View appointment <span aria-hidden="true">→</span></a>
                    </section>
                </div>

                <div class="treatment-grid">
                    <section class="surface-panel">
                        <h2>Treatment overview</h2>
                        <p class="panel-intro">Your current orthodontic plan</p>
                        <dl class="detail-list">
                            <div><dt>Treatment type</dt><dd>Fixed metal braces</dd></div>
                            <div><dt>Started</dt><dd>14 February 2025</dd></div>
                            <div><dt>Estimated completion</dt><dd>February 2027</dd></div>
                            <div><dt>Orthodontist</dt><dd>Dr. Sarah Lim</dd></div>
                        </dl>
                    </section>
                    <section class="surface-panel">
                        <h2>Recent milestones</h2>
                        <p class="panel-intro">Notes from your orthodontic visits</p>
                        <div class="timeline">
                            <div class="timeline-item"><span class="timeline-dot"></span><div><strong>Wire changed and elastics reviewed</strong><small>24 July 2026 · Progress is on track; continue night-time elastic wear.</small></div></div>
                            <div class="timeline-item"><span class="timeline-dot"></span><div><strong>Alignment improving</strong><small>19 June 2026 · Lower arch alignment continues to improve.</small></div></div>
                            <div class="timeline-item"><span class="timeline-dot"></span><div><strong>Braces fitted</strong><small>14 February 2025 · Initial appliances placed.</small></div></div>
                        </div>
                    </section>
                </div>
            </section>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/f3ce044c74.js" crossorigin="anonymous"></script>
</body>
</html>