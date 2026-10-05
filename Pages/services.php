<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/x-icon" href="../Logo.png">
        <title>Services | BrightSmile Dental Clinic</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="../Styling/services.css">
    </head>

    <body>
        <!-- Main application -->
        <div class="app-container">

            <!-- Sidebar -->
            <?php
                include '../Components/Sidebar.php';
            ?>

            <!-- Main content -->
            <main class="main-content">

                <!-- Header -->
                <?php
                    include '../Components/Header.php';
                ?>

                <!-- Page content -->
                <div class="page-content">

                    <section class="page-header">
                        <div>
                            <span class="page-label">
                                CLINIC SERVICES
                            </span>

                            <h1>
                                Dental Services & Fees
                            </h1>

                            <p>
                                View the dental services available at BrightSmile
                                and their standard consultation fees.
                            </p>
                        </div>
                    </section>

                    <!-- General dentistry -->
                    <section class="service-section">
                        <div class="section-title">
                            <span class="section-number">
                                01
                            </span>

                            <div>
                                <h2>
                                    General Dentistry
                                </h2>

                                <p>
                                    Routine care and common dental treatments
                                </p>
                            </div>
                        </div>

                        <div class="service-grid">
                            <div class="service-card">
                                <div class="service-icon">
                                    +
                                </div>

                                <div class="service-info">
                                    <h3>
                                        Dental Consultation
                                    </h3>

                                    <p>
                                        General examination and consultation
                                        with a dentist.
                                    </p>

                                    <div class="service-bottom">
                                        <span>
                                            From
                                        </span>

                                        <strong>
                                            RM 30
                                        </strong>
                                    </div>
                                </div>
                            </div>

                            <div class="service-card">
                                <div class="service-icon">
                                    ✓
                                </div>

                                <div class="service-info">
                                    <h3>
                                        Dental Cleaning
                                    </h3>

                                    <p>
                                        Professional cleaning to remove
                                        plaque and tartar.
                                    </p>

                                    <div class="service-bottom">
                                        <span>
                                            From
                                        </span>

                                        <strong>
                                            RM 80
                                        </strong>
                                    </div>
                                </div>
                            </div>

                            <div class="service-card">
                                <div class="service-icon">
                                    ◇
                                </div>

                                <div class="service-info">
                                    <h3>
                                        Dental Filling
                                    </h3>

                                    <p>
                                        Restore teeth affected by decay
                                        or minor damage.
                                    </p>

                                    <div class="service-bottom">
                                        <span>
                                            From
                                        </span>

                                        <strong>
                                            RM 90
                                        </strong>
                                    </div>
                                </div>
                            </div>

                            <div class="service-card">
                                <div class="service-icon">
                                    □
                                </div>

                                <div class="service-info">
                                    <h3>
                                        Tooth Extraction
                                    </h3>

                                    <p>
                                        Removal of a damaged or problematic
                                        tooth when required.
                                    </p>

                                    <div class="service-bottom">
                                        <span>
                                            From
                                        </span>

                                        <strong>
                                            RM 100
                                        </strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Orthodontics -->

                    <section class="service-section">
                        <div class="section-title">
                            <span class="section-number">
                                02
                            </span>

                            <div>
                                <h2>
                                    Orthodontics
                                </h2>

                                <p>
                                    Treatments for teeth alignment and bite correction
                                </p>
                            </div>
                        </div>

                        <div class="service-grid">
                            <div class="service-card">
                                <div class="service-icon">
                                    ≋
                                </div>

                                <div class="service-info">
                                    <h3>
                                        Orthodontic Consultation
                                    </h3>

                                    <p>
                                        Assessment and consultation for
                                        orthodontic treatment.
                                    </p>

                                    <div class="service-bottom">
                                        <span>
                                            From
                                        </span>

                                        <strong>
                                            RM 50
                                        </strong>
                                    </div>
                                </div>
                            </div>

                            <div class="service-card">
                                <div class="service-icon">
                                    ⊕
                                </div>

                                <div class="service-info">
                                    <h3>
                                        Braces Adjustment
                                    </h3>

                                    <p>
                                        Regular adjustment and monitoring
                                        during braces treatment.
                                    </p>

                                    <div class="service-bottom">
                                        <span>
                                            From
                                        </span>

                                        <strong>
                                            RM 120
                                        </strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Note -->
                    <div class="price-note">
                        <span>ⓘ</span>

                        <p>
                            Prices shown are standard starting prices and may
                            vary depending on the patient's treatment needs.
                            Please consult our dentist for an accurate quotation.
                        </p>

                    </div>
                </div>
            </main>
        </div>

        <script src="https://kit.fontawesome.com/f3ce044c74.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>