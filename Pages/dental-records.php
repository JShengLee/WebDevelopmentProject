<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../Logo.png">
    <title>Dental Records | BrightSmile Dental Clinic</title>
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
                        <p class="page-label">YOUR CARE HISTORY</p>
                        <h1>Dental Records</h1>
                        <p class="page-description">A clear history of your visits, treatments, and care notes.</p>
                    </div>
                    <a class="primary-button" href="appointments.php">View appointments <span aria-hidden="true">→</span></a>
                </div>

                <section class="metric-strip" aria-label="Dental record summary">
                    <div><span>RECORDED VISITS</span><strong>4</strong><small>Since February 2025</small></div>
                    <div><span>ACTIVE CARE PLAN</span><strong>1</strong><small>Orthodontic treatment</small></div>
                    <div><span>LAST VISIT</span><strong>21</strong><small>August 2026 · Dental cleaning</small></div>
                </section>

                <section class="table-panel">
                    <div class="table-toolbar">
                        <div><h2>Visit history</h2><p>Clinical records available in your patient profile</p></div>
                        <label>
                            <span class="visually-hidden">Filter records by type</span>
                            <select id="record-filter" class="table-filters-select">
                                <option value="all">All record types</option>
                                <option value="orthodontics">Orthodontics</option>
                                <option value="general">General dentistry</option>
                            </select>
                        </label>
                    </div>
                    <div class="table-scroll">
                        <table class="data-table" id="records-table">
                            <thead><tr><th>Date</th><th>Visit</th><th>Provider</th><th>Clinical note</th><th>Record type</th></tr></thead>
                            <tbody>
                                <tr data-type="general"><td>21 Aug 2026</td><td><strong>Dental cleaning</strong><small>Preventive care</small></td><td>Dr. Marcus Tan</td><td>Routine scale and polish completed.</td><td>General dentistry</td></tr>
                                <tr data-type="orthodontics"><td>24 Jul 2026</td><td><strong>Orthodontic review</strong><small>Follow-up visit</small></td><td>Dr. Sarah Lim</td><td>Alignment progressing well; elastics reviewed.</td><td>Orthodontics</td></tr>
                                <tr data-type="orthodontics"><td>19 Jun 2026</td><td><strong>Braces adjustment</strong><small>Adjustment visit</small></td><td>Dr. Sarah Lim</td><td>Wire adjusted; continue prescribed elastic wear.</td><td>Orthodontics</td></tr>
                                <tr data-type="general"><td>12 Mar 2026</td><td><strong>Dental examination</strong><small>Routine check-up</small></td><td>Dr. Marcus Tan</td><td>No new concerns noted during examination.</td><td>General dentistry</td></tr>
                            </tbody>
                        </table>
                        <p class="empty-state" id="records-empty" hidden>No records match this type.</p>
                    </div>
                    <div class="table-footnote">Records are provided for your personal reference. Contact the clinic for corrections.</div>
                </section>
            </section>
        </main>
    </div>
    <script>
        const recordFilter = document.getElementById('record-filter');
        const recordRows = [...document.querySelectorAll('#records-table tbody tr')];
        const recordsEmpty = document.getElementById('records-empty');

        recordFilter.addEventListener('change', () => {
            let visibleCount = 0;
            recordRows.forEach((row) => {
                row.hidden = recordFilter.value !== 'all' && row.dataset.type !== recordFilter.value;
                if (!row.hidden) visibleCount += 1;
            });
            recordsEmpty.hidden = visibleCount > 0;
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/f3ce044c74.js" crossorigin="anonymous"></script>
</body>
</html>