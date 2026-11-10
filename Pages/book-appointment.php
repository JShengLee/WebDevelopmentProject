<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../Logo.png">
    <title>Book Appointment | BrightSmile Dental Clinic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Styling/appointments.css">
</head>
<body>
    <!-- Main application -->
    <div class="app-container">

        <!-- Sidebar -->
        <?php
        require_once __DIR__ . '/../includes/patient_bootstrap.php';
        $flash = take_flash();
        $minimumAppointmentDate = (new DateTimeImmutable('today'))->modify('+2 days')->format('Y-m-d');
        $services = $pdo->query('SELECT ServiceID, ServiceName FROM service ORDER BY ServiceName')->fetchAll();
        $dentists = $pdo->query(
            "SELECT d.DentistID, u.FullName FROM dentist d INNER JOIN user u ON u.UserID = d.UserID WHERE u.Role = 'Dentist' ORDER BY u.FullName"
        )->fetchAll();
        ?>

        <!-- Main content -->
        <main class="main-content">

            <!-- Header -->
            <?php
                include '../Components/Header.php';
            ?>

            <!-- Page content -->
            <section class="page-content">
                <div class="page-heading">
                    <h1>Book an appointment</h1>
                    <p class="page-description">Choose a service and preferred time for your next visit.</p>
                </div>
                <div class="booking-layout">
                    <section class="booking-card">
                        <div class="card-heading"><div><h2>Appointment details</h2><p>Requests are confirmed by the clinic.</p></div><span class="step-number">1</span></div>
                        <?php if ($flash): ?><p role="status" class="form-message"><?= escape_html($flash['message']) ?></p><?php endif; ?>
                        <?php if (!$services || !$dentists): ?><p role="status" class="form-message">Appointments are unavailable until services and dentists are configured.</p><?php endif; ?>
                        <form id="appointmentForm" action="save-appointment.php" method="post">
                            <input type="hidden" name="csrf_token" value="<?= escape_html(csrf_token()) ?>">
                            <div class="form-group">
                                <label for="service">Select service</label>
                                <select id="service" name="service_id" required>
                                    <option value="">Choose a dental service</option>
                                    <?php foreach ($services as $serviceOption): ?>
                                        <option value="<?= (int) $serviceOption['ServiceID'] ?>"><?= escape_html($serviceOption['ServiceName']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="dentist">Preferred dentist</label>
                                <select id="dentist" name="dentist_id" required>
                                    <option value="">Choose a dentist</option>
                                    <?php foreach ($dentists as $dentistOption): ?>
                                        <option value="<?= (int) $dentistOption['DentistID'] ?>"><?= escape_html($dentistOption['FullName']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="appointment-date">Select date</label>
                                <input type="date" id="appointment-date" name="appointment_date" min="<?= escape_html($minimumAppointmentDate) ?>" required>
                                <small>Choose a date at least two days from today.</small>
                            </div>
                            <div class="form-group">
                                <label>Available time</label>
                                <div class="time-slots" role="group" aria-label="Available appointment times">
                                    <button type="button" class="time-slot" data-time="09:00">9:00 AM</button><button type="button" class="time-slot" data-time="10:00">10:00 AM</button><button type="button" class="time-slot" data-time="11:00">11:00 AM</button><button type="button" class="time-slot" data-time="14:00">2:00 PM</button><button type="button" class="time-slot" data-time="15:00">3:00 PM</button><button type="button" class="time-slot" data-time="16:00">4:00 PM</button>
                                </div>
                                <input type="hidden" id="selected-time" name="appointment_time" required>
                            </div>
                            <div class="form-group">
                                <label for="reason">Reason for visit <span>(optional)</span></label>
                                <textarea id="reason" name="notes" rows="3" placeholder="Share anything the dentist should know..."></textarea>
                            </div>
                            <div class="form-actions">
                                <a class="cancel-button" href="appointments.php">Cancel</a>
                                <button class="confirm-button" type="submit" <?= !$services || !$dentists ? 'disabled' : '' ?>>Book appointment</button>
                            </div>
                        </form>
                    </section>
                    <aside class="summary-column">
                        <section class="summary-card">
                            <div class="card-heading"><div><h2>Appointment summary</h2><p>Review your choices before sending.</p></div><span class="step-number">2</span></div>
                            <div class="summary-content">
                                <div class="summary-row"><span>Service</span><strong id="summary-service">Not selected</strong></div>
                                <div class="summary-row"><span>Dentist</span><strong id="summary-dentist">Not selected</strong></div>
                                <div class="summary-row"><span>Date</span><strong id="summary-date">Not selected</strong></div>
                                <div class="summary-row"><span>Time</span><strong id="summary-time">Not selected</strong></div>
                                <div class="status-row"><span>Request status</span><span class="pending-badge">Pending</span></div>
                            </div>
                        </section>
                        <section class="notice-card"><div class="notice-icon" aria-hidden="true">!</div><div><h3>Before your appointment</h3><ul><li>Arrive 10–15 minutes early.</li><li>Bring identification and relevant dental records.</li><li>The clinic will confirm your requested time.</li></ul></div></section>
                    </aside>
                </div>
            </section>
        </main>
    </div>
    <script>
        const service = document.getElementById('service');
        const dentist = document.getElementById('dentist');
        const date = document.getElementById('appointment-date');
        const selectedTime = document.getElementById('selected-time');
        const timeSlots = [...document.querySelectorAll('.time-slot')];
        const summary = {
            service: document.getElementById('summary-service'),
            dentist: document.getElementById('summary-dentist'),
            date: document.getElementById('summary-date'),
            time: document.getElementById('summary-time')
        };

        service.addEventListener('change', () => summary.service.textContent = service.selectedOptions[0]?.textContent || 'Not selected');
        dentist.addEventListener('change', () => summary.dentist.textContent = dentist.selectedOptions[0]?.textContent || 'Not selected');
        date.addEventListener('change', () => {
            summary.date.textContent = date.value
                ? new Date(`${date.value}T00:00:00`).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
                : 'Not selected';
        });
        function localDateString(value) {
            const year = value.getFullYear();
            const month = String(value.getMonth() + 1).padStart(2, '0');
            const day = String(value.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        const minimumDate = new Date();
        minimumDate.setHours(0, 0, 0, 0);
        minimumDate.setDate(minimumDate.getDate() + 2);
        date.min = localDateString(minimumDate);

        timeSlots.forEach((slot) => slot.addEventListener('click', () => {
            timeSlots.forEach((item) => item.classList.remove('selected'));
            slot.classList.add('selected');
            selectedTime.value = slot.dataset.time;
            summary.time.textContent = slot.textContent.trim();
        }));

        document.getElementById('appointmentForm').addEventListener('submit', (event) => {
            if (!selectedTime.value) {
                alert('Please select an available appointment time.');
                event.preventDefault();
                return;
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/f3ce044c74.js" crossorigin="anonymous"></script>
</body>
</html>