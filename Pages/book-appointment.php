<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../Logo.png">
    <title>Book Appointment | BrightSmile Dental Clinic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Styling/appointments.css?v=1">
</head>
<body>
    <!-- Main application -->
    <div class="app-container">

        <!-- Sidebar -->
        <?php
        require_once __DIR__ . '/../includes/patient_bootstrap.php';
        $flash = take_flash();
        $minimumAppointmentDate = (new DateTimeImmutable('today'))->modify('+2 days')->format('Y-m-d');
        $services = $pdo->query('SELECT ServiceID, ServiceName, Category, Duration FROM service ORDER BY ServiceName')->fetchAll();
        $dentists = $pdo->query(
            "SELECT d.DentistID, u.FullName, d.Specialty, dep.DepartmentName
             FROM dentist d
             INNER JOIN user u ON u.UserID = d.UserID
             LEFT JOIN department dep ON dep.DeptID = d.DeptID
             WHERE u.Role = 'Dentist'
             ORDER BY d.DentistID ASC, u.FullName ASC"
        )->fetchAll();
        $appointmentBlocks = $pdo->query(
            "SELECT a.DentistID, a.AppointmentDate, a.AppointmentTime, a.AppointmentStatus,
                    s.Duration AS ServiceDurationMinutes
             FROM appointment a
             INNER JOIN service s ON s.ServiceID = a.ServiceID
             WHERE a.AppointmentStatus NOT IN ('Cancelled', 'Rejected')
             ORDER BY a.AppointmentDate, a.AppointmentTime"
        )->fetchAll();
        $normalizeCategory = static function (?string $category): string {
            $category = strtolower(trim($category ?? ''));
            if ($category === 'general') {
                return 'general';
            }
            return str_starts_with($category, 'ortho') ? 'orthodontics' : '';
        };
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
                        <div class="card-heading"><div><h2>Appointment details</h2><p>Your appointment is confirmed when booked.</p></div><span class="step-number">1</span></div>
                        <?php if ($flash): ?><p role="status" class="form-message"><?= escape_html($flash['message']) ?></p><?php endif; ?>
                        <?php if (!$services || !$dentists): ?><p role="status" class="form-message">Appointments are unavailable until services and dentists are configured.</p><?php endif; ?>
                        <form id="appointmentForm" action="save-appointment.php" method="post">
                            <input type="hidden" name="csrf_token" value="<?= escape_html(csrf_token()) ?>">
                            <div class="form-group">
                                <label for="service">Select service</label>
                                <select id="service" name="service" required>
                                    <option value="">Choose a dental service</option>
                                    <?php foreach ($services as $serviceOption): ?>
                                        <option value="<?= (int) $serviceOption['ServiceID'] ?>" data-category="<?= escape_html($normalizeCategory($serviceOption['Category'])) ?>" data-duration-minutes="<?= (int) ($serviceOption['Duration'] ?? 0) ?>"><?= escape_html($serviceOption['ServiceName']) ?> (<?= (int) ($serviceOption['Duration'] ?? 0) ?> min)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="dentist">Preferred dentist</label>
                                <select id="dentist" name="dentist" required disabled>
                                    <option value="">Please select a service first</option>
                                    <?php foreach ($dentists as $dentistOption): ?>
                                        <?php
                                            $dentistCategories = array_filter([
                                                $normalizeCategory($dentistOption['DepartmentName']),
                                                $normalizeCategory($dentistOption['Specialty']),
                                            ]);
                                        ?>
                                        <option value="<?= (int) $dentistOption['DentistID'] ?>" data-categories="<?= escape_html(implode('|', array_unique($dentistCategories))) ?>"><?= escape_html($dentistOption['FullName']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="date-picker-trigger">Select date</label>
                                <div class="date-picker" id="appointment-date-picker">
                                    <input type="hidden" id="appointment-date" name="appointment_date" value="" data-min-date="<?= escape_html($minimumAppointmentDate) ?>">
                                    <button type="button" class="date-picker-trigger" id="date-picker-trigger" aria-haspopup="dialog" aria-expanded="false" aria-controls="appointment-calendar">
                                        <span id="date-picker-value">Choose an available date</span>
                                        <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                                    </button>
                                    <div class="date-picker-calendar" id="appointment-calendar" role="dialog" aria-label="Choose appointment date" hidden>
                                        <div class="date-picker-header">
                                            <button type="button" id="calendar-previous" aria-label="Previous month">‹</button>
                                            <h2 id="calendar-month" aria-live="polite"></h2>
                                            <button type="button" id="calendar-next" aria-label="Next month">›</button>
                                        </div>
                                        <div class="calendar-grid calendar-weekdays" aria-hidden="true">
                                            <span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span>
                                        </div>
                                        <div class="calendar-grid" id="calendar-days"></div>
                                    </div>
                                </div>
                                <small>Choose a date at least two days from today.</small>
                            </div>
                            <div class="form-group">
                                <label>Available time</label>
                                <div class="time-slots" role="group" aria-label="Available appointment times">
                                    <button type="button" class="time-slot" data-time="09:00">9:00 AM</button><button type="button" class="time-slot" data-time="10:00">10:00 AM</button><button type="button" class="time-slot" data-time="11:00">11:00 AM</button><button type="button" class="time-slot" data-time="14:00">2:00 PM</button><button type="button" class="time-slot" data-time="15:00">3:00 PM</button><button type="button" class="time-slot" data-time="16:00">4:00 PM</button>
                                </div>
                                <p id="no-time-slots" class="form-message" role="status" hidden>No available time slots for this dentist, service, and date.</p>
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
                                <div class="status-row"><span>Appointment status</span><span class="confirmed-badge">Confirmed</span></div>
                            </div>
                        </section>
                        <section class="notice-card"><div class="notice-icon" aria-hidden="true">!</div><div><h3>Before your appointment</h3><ul><li>Arrive 10–15 minutes early.</li><li>Bring identification and relevant dental records.</li><li>Your appointment is confirmed once booked.</li></ul></div></section>
                    </aside>
                </div>
            </section>
        </main>
    </div>
    <script>
        const service = document.getElementById('service');
        const dentist = document.getElementById('dentist');
        const date = document.getElementById('appointment-date');
        const datePicker = document.getElementById('appointment-date-picker');
        const datePickerTrigger = document.getElementById('date-picker-trigger');
        const datePickerValue = document.getElementById('date-picker-value');
        const calendar = document.getElementById('appointment-calendar');
        const calendarMonth = document.getElementById('calendar-month');
        const calendarDays = document.getElementById('calendar-days');
        const previousMonthButton = document.getElementById('calendar-previous');
        const nextMonthButton = document.getElementById('calendar-next');
        const selectedTime = document.getElementById('selected-time');
        const timeSlots = [...document.querySelectorAll('.time-slot')];
        const noTimeSlotsMessage = document.getElementById('no-time-slots');
        const summary = {
            service: document.getElementById('summary-service'),
            dentist: document.getElementById('summary-dentist'),
            date: document.getElementById('summary-date'),
            time: document.getElementById('summary-time')
        };

        const allDentistOptions = [...dentist.options].slice(1);
        const dentistPrompt = dentist.options[0];
        const normalizeCategory = (category) => {
            const normalized = (category || '').trim().toLowerCase();
            if (normalized === 'general') return 'general';
            return normalized.startsWith('ortho') ? 'orthodontics' : '';
        };

        const timeSlotMinutes = {
            '09:00': 9 * 60,
            '10:00': 10 * 60,
            '11:00': 11 * 60,
            '14:00': 14 * 60,
            '15:00': 15 * 60,
            '16:00': 16 * 60,
        };
        window.appointmentBlocks = <?php echo json_encode($appointmentBlocks, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
        const minimumDate = new Date(`${date.dataset.minDate}T00:00:00`);
        let visibleMonth = new Date(minimumDate.getFullYear(), minimumDate.getMonth(), 1);

        function localDateString(value) {
            const year = value.getFullYear();
            const month = String(value.getMonth() + 1).padStart(2, '0');
            const day = String(value.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        function renderCalendar() {
            calendarMonth.textContent = visibleMonth.toLocaleDateString('en-GB', { month: 'long', year: 'numeric' });
            previousMonthButton.disabled = visibleMonth.getFullYear() === minimumDate.getFullYear()
                && visibleMonth.getMonth() === minimumDate.getMonth();
            calendarDays.replaceChildren();

            const firstDay = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth(), 1);
            const offset = (firstDay.getDay() + 6) % 7;
            const daysInMonth = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth() + 1, 0).getDate();

            for (let index = 0; index < offset; index += 1) {
                const spacer = document.createElement('span');
                spacer.className = 'calendar-day-spacer';
                spacer.setAttribute('aria-hidden', 'true');
                calendarDays.appendChild(spacer);
            }

            for (let day = 1; day <= daysInMonth; day += 1) {
                const dayDate = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth(), day);
                const dateValue = localDateString(dayDate);
                const isSunday = dayDate.getDay() === 0;
                const dayButton = document.createElement('button');
                dayButton.type = 'button';
                dayButton.className = 'calendar-day';
                dayButton.textContent = String(day);
                dayButton.disabled = isSunday || dateValue < date.dataset.minDate;
                dayButton.setAttribute('aria-label', dayDate.toLocaleDateString('en-GB', {
                    weekday: 'long',
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                }));
                if (isSunday) {
                    dayButton.classList.add('calendar-day-closed');
                    dayButton.title = 'Clinic closed';
                }
                if (dateValue === date.value) {
                    dayButton.classList.add('calendar-day-selected');
                    dayButton.setAttribute('aria-pressed', 'true');
                }
                dayButton.addEventListener('click', () => {
                    date.value = dateValue;
                    datePickerValue.textContent = dayDate.toLocaleDateString('en-GB', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric'
                    });
                    closeCalendar();
                    updateSummaryDate();
                    applyAvailableSlots();
                });
                calendarDays.appendChild(dayButton);
            }
        }

        function openCalendar() {
            if (date.value) {
                const selectedDate = new Date(`${date.value}T00:00:00`);
                visibleMonth = new Date(selectedDate.getFullYear(), selectedDate.getMonth(), 1);
            }
            renderCalendar();
            calendar.hidden = false;
            datePickerTrigger.setAttribute('aria-expanded', 'true');
        }

        function closeCalendar() {
            calendar.hidden = true;
            datePickerTrigger.setAttribute('aria-expanded', 'false');
        }

        function updateSummaryDate() {
            summary.date.textContent = date.value
                ? new Date(`${date.value}T00:00:00`).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
                : 'Not selected';
        }

        function toMinutes(timeString) {
            const [hours, minutes] = (timeString || '00:00').split(':').map(Number);
            return hours * 60 + minutes;
        }

        function overlaps(startA, endA, startB, endB) {
            return startA < endB && startB < endA;
        }

        function applyAvailableSlots() {
            const selectedDentist = dentist.value;
            const selectedDate = date.value;
            const selectedService = service.selectedOptions[0];

            timeSlots.forEach((slot) => {
                slot.disabled = false;
                slot.hidden = false;
                slot.classList.remove('disabled', 'selected');
                slot.setAttribute('aria-disabled', 'false');
            });
            noTimeSlotsMessage.hidden = true;
            selectedTime.value = '';
            summary.time.textContent = 'Not selected';

            if (!selectedDentist || !selectedDate || !selectedService || !selectedService.value) {
                timeSlots.forEach((slot) => {
                    slot.disabled = true;
                    slot.hidden = true;
                    slot.classList.add('disabled');
                    slot.setAttribute('aria-disabled', 'true');
                });
                return;
            }

            const requiredMinutes = Math.max(1, Number(selectedService.dataset.durationMinutes || 60));
            const selectedDateBookings = window.appointmentBlocks
                ? window.appointmentBlocks.filter((booking) => Number(booking.DentistID) === Number(selectedDentist) && booking.AppointmentDate === selectedDate && booking.AppointmentStatus !== 'Cancelled' && booking.AppointmentStatus !== 'Rejected')
                : [];

            timeSlots.forEach((slot) => {
                const slotStartMinutes = timeSlotMinutes[slot.dataset.time];
                const slotEndMinutes = slotStartMinutes + requiredMinutes;
                const hasOverlap = selectedDateBookings.some((booking) => {
                    const bookedStartMinutes = toMinutes(booking.AppointmentTime);
                    const durationMinutes = Number(booking.ServiceDurationMinutes || 60);
                    const bookedEndMinutes = bookedStartMinutes + durationMinutes;
                    return overlaps(slotStartMinutes, slotEndMinutes, bookedStartMinutes, bookedEndMinutes);
                });

                if (hasOverlap) {
                    slot.disabled = true;
                    slot.hidden = true;
                    slot.classList.add('disabled');
                    slot.setAttribute('aria-disabled', 'true');
                    return;
                }

                slot.disabled = false;
                slot.setAttribute('aria-disabled', 'false');
            });
            noTimeSlotsMessage.hidden = !timeSlots.every((slot) => slot.hidden);
        }

        service.addEventListener('change', () => {
            const selectedService = service.selectedOptions[0];
            const category = normalizeCategory(selectedService?.dataset.category);
            const matchingDentists = category
                ? allDentistOptions.filter((option) => option.dataset.categories.split('|').includes(category))
                : [];

            dentist.replaceChildren(dentistPrompt.cloneNode(true));
            matchingDentists.forEach((option) => dentist.appendChild(option.cloneNode(true)));
            dentist.options[0].textContent = category
                ? (matchingDentists.length ? 'Choose a dentist' : 'No dentists available for this service')
                : 'Please select a service first';
            dentist.value = '';
            dentist.disabled = !category || matchingDentists.length === 0;
            summary.service.textContent = selectedService?.value ? selectedService.textContent : 'Not selected';
            summary.dentist.textContent = 'Not selected';
            applyAvailableSlots();
        });

        dentist.addEventListener('change', () => {
            summary.dentist.textContent = dentist.selectedOptions[0]?.textContent || 'Not selected';
            applyAvailableSlots();
        });

        datePickerTrigger.addEventListener('click', () => {
            if (calendar.hidden) {
                openCalendar();
            } else {
                closeCalendar();
            }
        });

        previousMonthButton.addEventListener('click', () => {
            visibleMonth = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth() - 1, 1);
            renderCalendar();
        });

        nextMonthButton.addEventListener('click', () => {
            visibleMonth = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth() + 1, 1);
            renderCalendar();
        });

        document.addEventListener('click', (event) => {
            if (!datePicker.contains(event.target)) {
                closeCalendar();
            }
        });

        datePicker.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !calendar.hidden) {
                closeCalendar();
                datePickerTrigger.focus();
            }
        });

        timeSlots.forEach((slot) => slot.addEventListener('click', () => {
            if (slot.disabled) {
                return;
            }
            timeSlots.forEach((item) => item.classList.remove('selected'));
            slot.classList.add('selected');
            selectedTime.value = slot.dataset.time;
            summary.time.textContent = slot.textContent.trim();
        }));

        document.getElementById('appointmentForm').addEventListener('submit', (event) => {
            if (!date.value) {
                alert('Please select an available appointment date.');
                event.preventDefault();
                return;
            }
            if (!selectedTime.value) {
                alert('Please select an available appointment time.');
                event.preventDefault();
                return;
            }
        });

        updateSummaryDate();
        applyAvailableSlots();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/f3ce044c74.js" crossorigin="anonymous"></script>
</body>
</html>