<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="icon" type="image/x-icon" href="../Logo.png">
            <title>Appointments | BrightSmile Dental Clinic</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="../Styling/appointments.css">
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
                <?php
                    include '../Components/Header.php';
                ?>

                <!-- Page content -->
                <section class="page-content">
                    <!-- Page Heading -->
                    <div class="page-heading">
                        <div>
                            <p class="page-label">APPOINTMENTS</p>

                            <h1>Book Appointment</h1>

                            <p class="page-description">
                                Schedule your next dental visit with BrightSmile Dental Clinic.
                            </p>
                        </div>
                    </div>

                    <!-- Booking area -->
                    <div class="booking-layout">

                        <!-- Left: booking form -->
                        <div class="booking-card">
                            <div class="card-heading">
                                <div>
                                    <h2>Appointment Details</h2>
                                    <p>
                                        Please provide the details for your appointment.
                                    </p>
                                </div>
                                <span class="step-number">1</span>
                            </div>

                            <form id="appointmentForm">
                                <!-- Service -->
                                <div class="form-group">
                                    <label for="service">
                                        Select Service
                                    </label>

                                    <select id="service" name="service" required>
                                        <option value="">
                                            Select a dental service
                                        </option>

                                        <option value="Dental Consultation">
                                            Dental Consultation
                                        </option>

                                        <option value="Dental Cleaning">
                                            Dental Cleaning
                                        </option>

                                        <option value="Dental Filling">
                                            Dental Filling
                                        </option>

                                        <option value="Tooth Extraction">
                                            Tooth Extraction
                                        </option>

                                        <option value="Orthodontic Consultation">
                                            Orthodontic Consultation
                                        </option>

                                        <option value="Braces Adjustment">
                                            Braces Adjustment
                                        </option>
                                    </select>
                                </div>

                                <!-- Dentist -->
                                <div class="form-group">
                                    <label for="dentist">
                                        Preferred Dentist
                                    </label>

                                    <select id="dentist" name="dentist" required>
                                        <option value="">
                                            Select a dentist
                                        </option>

                                        <option value="Any Available Dentist">
                                            Any Available Dentist
                                        </option>

                                        <option value="Dr. Tan">
                                            Dr. Tan
                                        </option>

                                        <option value="Dr. Lim">
                                            Dr. Lim
                                        </option>

                                        <option value="Dr. Wong">
                                            Dr. Wong
                                        </option>
                                    </select>
                                </div>

                                <!-- Date -->
                                <div class="form-group">
                                    <label for="appointment-date">
                                        Select Date
                                    </label>

                                    <input
                                        type="date"
                                        id="appointment-date"
                                        name="appointment-date"
                                        required>
                                    <small>
                                        Please select a date from today onwards.
                                    </small>
                                </div>

                                <!-- Time -->
                                <div class="form-group">
                                    <label>
                                        Available Time
                                    </label>

                                    <div class="time-slots">
                                        <button type="button" class="time-slot">
                                            9:00 AM
                                        </button>

                                        <button type="button" class="time-slot">
                                            10:00 AM
                                        </button>

                                        <button type="button" class="time-slot">
                                            11:00 AM
                                        </button>

                                        <button type="button" class="time-slot">
                                            2:00 PM
                                        </button>

                                        <button type="button" class="time-slot">
                                            3:00 PM
                                        </button>

                                        <button type="button" class="time-slot">
                                            4:00 PM
                                        </button>
                                    </div>

                                    <input
                                        type="hidden"
                                        id="selected-time"
                                        name="selected-time"
                                        required>
                                    <small>
                                        Available times will depend on the selected dentist and date.
                                    </small>
                                </div>

                                <!-- Reason -->
                                <div class="form-group">
                                    <label for="reason">
                                        Reason for Visit
                                    </label>

                                    <textarea
                                        id="reason"
                                        name="reason"
                                        rows="4"
                                        placeholder="Briefly describe the reason for your visit..."></textarea>

                                </div>

                                <!-- Buttons -->
                                <div class="form-actions">
                                    <button
                                        type="reset"
                                        class="cancel-button">
                                        Clear
                                    </button>

                                    <button
                                        type="submit"
                                        class="confirm-button">
                                        Confirm Appointment
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Right: Summary -->
                        <div class="summary-column">
                            <!-- Appointment Summary -->
                            <div class="summary-card">
                                <div class="card-heading">
                                    <div>
                                        <h2>Appointment Summary</h2>

                                        <p>
                                            Review your appointment details.
                                        </p>
                                    </div>
                                    <span class="step-number">2</span>
                                </div>

                                <div class="summary-content">
                                    <div class="summary-row">
                                        <span>Service</span>

                                        <strong id="summary-service">
                                            Not selected
                                        </strong>
                                    </div>

                                    <div class="summary-row">
                                        <span>Dentist</span>
                                        <strong id="summary-dentist">
                                            Not selected
                                        </strong>
                                    </div>

                                    <div class="summary-row">
                                        <span>Date</span>

                                        <strong id="summary-date">
                                            Not selected
                                        </strong>
                                    </div>

                                    <div class="summary-row">
                                        <span>Time</span>

                                        <strong id="summary-time">
                                            Not selected
                                        </strong>
                                    </div>

                                    <div class="summary-divider"></div>

                                    <div class="status-row">
                                        <span>Appointment Status</span>

                                        <span class="pending-badge">
                                            Pending
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Important Information -->
                            <div class="notice-card">
                                <div class="notice-icon">
                                    !
                                </div>

                                <div>
                                    <h3>Before Your Appointment</h3>
                                    <ul>
                                        <li>
                                            Please arrive 10–15 minutes early.
                                        </li>
                                        <li>
                                            Bring your identification or relevant dental records.
                                        </li>
                                        <li>
                                            Your appointment will remain pending until confirmed by the clinic.
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        </div>

        <!-- Bootstrap JavaScript -->
        <script
            src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js">
        </script>

        <!-- Simple Appointment Interaction -->
        <script>
            const service = document.getElementById("service");
            const dentist = document.getElementById("dentist");
            const date = document.getElementById("appointment-date");

            const summaryService =
                document.getElementById("summary-service");

            const summaryDentist =
                document.getElementById("summary-dentist");

            const summaryDate =
                document.getElementById("summary-date");

            const summaryTime =
                document.getElementById("summary-time");

            const timeSlots =
                document.querySelectorAll(".time-slot");

            const selectedTime =
                document.getElementById("selected-time");

            /* Update service summary */
            service.addEventListener("change", function () {
                summaryService.textContent =
                    this.value || "Not selected";
            });

            /* Update dentist summary */
            dentist.addEventListener("change", function () {
                summaryDentist.textContent =
                    this.value || "Not selected";
            });

            /* Update date summary */
            date.addEventListener("change", function () {
                if (this.value) {
                    const selectedDate =
                        new Date(this.value + "T00:00:00");

                    summaryDate.textContent =
                        selectedDate.toLocaleDateString(
                            "en-GB",
                            {
                                day: "2-digit",
                                month: "short",
                                year: "numeric"
                            }
                        );
                } else {
                    summaryDate.textContent = "Not selected";
                }
            });

            /* Select appointment time */
            timeSlots.forEach(function (slot) {
                slot.addEventListener("click", function () {
                    timeSlots.forEach(function (item) {
                        item.classList.remove("selected");
                    });

                    this.classList.add("selected");

                    selectedTime.value =
                        this.textContent.trim();

                    summaryTime.textContent =
                        this.textContent.trim();
                });
            });

            /* Prevent selecting past dates */
            const today =
                new Date().toISOString().split("T")[0];
            date.setAttribute("min", today);

            /* Form submission */
            document.getElementById("appointmentForm")
                .addEventListener("submit", function (event) {
                    event.preventDefault();
                    if (!selectedTime.value) {
                        alert("Please select an available appointment time.");
                        return;
                    }
                    alert(
                        "Your appointment request has been submitted successfully."
                    );
                });

            /* Reset summary */
            document.getElementById("appointmentForm")
                .addEventListener("reset", function () {
                    setTimeout(function () {

                        summaryService.textContent = "Not selected";
                        summaryDentist.textContent = "Not selected";
                        summaryDate.textContent = "Not selected";
                        summaryTime.textContent = "Not selected";

                        timeSlots.forEach(function (slot) {
                            slot.classList.remove("selected");
                        });

                        selectedTime.value = "";
                    }, 0);
                });
        </script>

        <script src="https://kit.fontawesome.com/f3ce044c74.js" crossorigin="anonymous"></script>
    </body>
</html>