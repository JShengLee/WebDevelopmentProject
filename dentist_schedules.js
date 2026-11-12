// DENTIST APPOINTMENTS PAGE

document.addEventListener("DOMContentLoaded", function () {

    let currentAppointment = null;

    // Get page elements
    const dateInput = document.getElementById("appointmentDate");
    const previousButton = document.getElementById("previousDay");
    const nextButton = document.getElementById("nextDay");
    const todayButton = document.getElementById("todayButton");
    const scheduleStatusFilter = document.getElementById("scheduleStatusFilter");

    const timeline = document.getElementById("scheduleTimeline");
    const selectedDateText = document.getElementById("selectedDateText");
    const appointmentCountText = document.getElementById("appointmentCountText");

    const totalAppointments = document.getElementById("totalAppointments");
    const confirmedAppointments = document.getElementById("confirmedAppointments");
    const completedAppointments = document.getElementById("completedAppointments");

    const modal = document.getElementById("appointmentModal");
    const closeModalButton = document.getElementById("closeModal");
    const doneButton = document.getElementById("doneButton");
    const startVisitButton = document.getElementById("startVisitButton");

    // Save today's date
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    // Convert a Date object to YYYY-MM-DD using local time
    function formatDateInput(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, "0");
        const day = String(date.getDate()).padStart(2, "0");

        return `${year}-${month}-${day}`;
    }

    // Convert YYYY-MM-DD into a Date object without UTC conversion
    function parseDate(value) {
        const parts = value.split("-");

        return new Date(
            Number(parts[0]),
            Number(parts[1]) - 1,
            Number(parts[2])
        );
    }

    // Format the date for display
    function formatDisplayDate(date) {
        return date.toLocaleDateString("en-MY", {
            weekday: "long",
            day: "numeric",
            month: "long",
            year: "numeric"
        });
    }

    // Convert 24-hour time into 12-hour time
    function formatTime(time) {
        const parts = time.split(":");
        const hour = Number(parts[0]);
        const minute = parts[1];

        const period = hour >= 12 ? "PM" : "AM";
        const displayHour = hour % 12 || 12;

        return `${String(displayHour).padStart(2, "0")}:${minute} ${period}`;
    }

    
    // Get appointments for the selected database date.
    function getAppointmentsForDate(date) {
        return appointmentData.filter(function (appointment) {
            return appointment.date === date;
        });
    }


    // Apply the selected status filter
    function getFilteredAppointments(appointments) {
        const selectedStatus = scheduleStatusFilter.value;

        if (selectedStatus === "All") {
            return appointments;
        }

        return appointments.filter(function (appointment) {
            return appointment.status === selectedStatus;
        });
    }

    // Update the three summary cards
    function updateSummary(appointments) {
        totalAppointments.textContent = appointments.length;

        const confirmedCount = appointments.filter(function (appointment) {
            return appointment.status === "Confirmed";
        }).length;

        const completedCount = appointments.filter(function (appointment) {
            return appointment.status === "Completed";
        }).length;

        confirmedAppointments.textContent = confirmedCount;
        completedAppointments.textContent = completedCount;
    }

    // Create the HTML for one appointment
    function createAppointmentCard(appointment) {
        const button = document.createElement("button");

        button.type = "button";
        button.className =
            "schedule-appointment status-" +
            appointment.status.toLowerCase();

        button.setAttribute(
            "aria-label",
            `View appointment for ${appointment.patient}`
        );

        const avatar = document.createElement("div");
        avatar.className = "schedule-avatar";
        avatar.textContent = appointment.initials;

        const patientInfo = document.createElement("div");
        patientInfo.className = "schedule-patient-info";

        const patientName = document.createElement("strong");
        patientName.textContent = appointment.patient;

        const treatment = document.createElement("span");
        treatment.textContent = appointment.treatment;

        const status = document.createElement("span");
        status.className =
            "appointment-status " + appointment.status.toLowerCase();

        status.textContent = appointment.status;

        patientInfo.appendChild(patientName);
        patientInfo.appendChild(treatment);
        patientInfo.appendChild(status);

        const time = document.createElement("span");
        time.className = "schedule-time";
        time.textContent = formatTime(appointment.start);

        button.appendChild(avatar);
        button.appendChild(patientInfo);
        button.appendChild(time);

        // Open the appointment details popup
        button.addEventListener("click", function () {
            openAppointmentModal(appointment);
        });

        return button;
    }

    // Create the hourly timeline
    function renderTimeline(appointments) {
        timeline.innerHTML = "";

        // Display each hour from 9 AM to 6 PM
        for (let hour = 9; hour < 18; hour++) {

            const startTime =
                String(hour).padStart(2, "0") + ":00";

            const endTime =
                String(hour + 1).padStart(2, "0") + ":00";

            const row = document.createElement("div");
            row.className = "hour-row";

            const hourLabel = document.createElement("div");
            hourLabel.className = "hour-label";
            hourLabel.textContent = formatTime(startTime);

            const content = document.createElement("div");
            content.className = "hour-content";

            const appointmentContainer = document.createElement("div");
            appointmentContainer.className = "hour-appointments";

            // Find appointments starting within this hour
            const hourlyAppointments = appointments.filter(function (appointment) {
                return appointment.start < endTime && appointment.end > startTime;
            });

            if (hourlyAppointments.length === 0) {
                const emptySlot = document.createElement("div");
                emptySlot.className = "empty-slot";
                emptySlot.textContent = "No appointments scheduled";

                appointmentContainer.appendChild(emptySlot);
            } else {
                hourlyAppointments.forEach(function (appointment) {
                    appointmentContainer.appendChild(
                        createAppointmentCard(appointment)
                    );
                });
            }

            content.appendChild(appointmentContainer);

            row.appendChild(hourLabel);
            row.appendChild(content);

            timeline.appendChild(row);
        }

        // Add the final 6 PM time label
        const finalRow = document.createElement("div");
        finalRow.className = "hour-row";

        const finalLabel = document.createElement("div");
        finalLabel.className = "hour-label";
        finalLabel.textContent = "06:00 PM";

        const finalContent = document.createElement("div");
        finalContent.className = "hour-content";

        const finalEmptySlot = document.createElement("div");
        finalEmptySlot.className = "empty-slot";
        finalEmptySlot.textContent = "End of schedule";

        finalContent.appendChild(finalEmptySlot);
        finalRow.appendChild(finalLabel);
        finalRow.appendChild(finalContent);

        timeline.appendChild(finalRow);
    }

    // Refresh the page according to the selected date and filter
    function renderAppointments() {
        const selectedDate = dateInput.value;

        if (!selectedDate) {
            return;
        }

        const date = parseDate(selectedDate);

        // Get every appointment on the selected date
        const allAppointments = getAppointmentsForDate(selectedDate);

        // Apply the selected status filter
        const filteredAppointments =
            getFilteredAppointments(allAppointments);

        // Update the date heading
        if (selectedDate === formatDateInput(today)) {
            selectedDateText.textContent = "Today's Schedule";
        } else {
            selectedDateText.textContent = formatDisplayDate(date);
        }

        // Update summary cards using all appointments for this date
        updateSummary(allAppointments);

        // Update the filtered appointment count
        appointmentCountText.textContent =
            `${filteredAppointments.length} appointment` +
            `${filteredAppointments.length !== 1 ? "s" : ""} displayed`;

        // Draw the hourly timeline
        renderTimeline(filteredAppointments);
    }

    // Move the selected date forward or backward
    function changeDate(numberOfDays) {
        const selectedDate = parseDate(dateInput.value);

        selectedDate.setDate(
            selectedDate.getDate() + numberOfDays
        );

        dateInput.value = formatDateInput(selectedDate);

        renderAppointments();
    }

    // Fill in the appointment details popup
    function openAppointmentModal(appointment) {
        currentAppointment = appointment; // Store selected appointment

        const date = parseDate(appointment.date);

        document.getElementById("modalPatientName").textContent = appointment.patient;
        document.getElementById("modalPatientId").textContent = appointment.patient_id;
        document.getElementById("modalAppointmentId").textContent = appointment.id;
        document.getElementById("modalDate").textContent = formatDisplayDate(date);
        document.getElementById("modalTime").textContent =
            formatTime(appointment.start) + " - " + formatTime(appointment.end);
        document.getElementById("modalTreatment").textContent = appointment.treatment;
        document.getElementById("modalPhone").textContent = appointment.phone;
        document.getElementById("modalStatus").textContent = appointment.status;
        document.getElementById("modalNotes").textContent = appointment.notes;

        // Show 'Start New Visit' only for today's confirmed appointments without a record.
        if (startVisitButton) {
            const isConfirmed = appointment.status.toLowerCase() === "confirmed";
            const isToday = appointment.date === formatDateInput(today);
            const hasExistingRecord = Boolean(appointment.has_record);

            if (isToday && isConfirmed && !hasExistingRecord) {
                startVisitButton.style.display = "inline-flex";
            } else {
                startVisitButton.style.display = "none";
            }
        }

        modal.classList.add("show");
        modal.setAttribute("aria-hidden", "false");

        closeModalButton.focus();
    }

    // Close the popup
    function closeAppointmentModal() {
        modal.classList.remove("show");
        modal.setAttribute("aria-hidden", "true");
    }

    // Button event listeners
    previousButton.addEventListener("click", function () {
        changeDate(-1);
    });

    nextButton.addEventListener("click", function () {
        changeDate(1);
    });

    todayButton.addEventListener("click", function () {
        dateInput.value = formatDateInput(today);
        renderAppointments();
    });

    dateInput.addEventListener("change", renderAppointments);

    scheduleStatusFilter.addEventListener("change", renderAppointments);

    closeModalButton.addEventListener(
        "click",
        closeAppointmentModal
    );

    doneButton.addEventListener(
        "click",
        closeAppointmentModal
    );

    // Close the popup when clicking outside it
    modal.addEventListener("click", function (event) {
        if (event.target === modal) {
            closeAppointmentModal();
        }
    });

    // Close the popup with the Escape key
    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape" && modal.classList.contains("show")) {
            closeAppointmentModal();
        }
    });

    // Set today's date and display the initial schedule
    dateInput.value = formatDateInput(today);

    renderAppointments();

    if (startVisitButton) {
        startVisitButton.addEventListener("click", function () {
            if (!currentAppointment) return;

            // Redirect to your patient dental records page with query parameters
            const targetPage = 'dentist_patient_record.php'; 
            
            const appointmentId = currentAppointment.raw_id || String(currentAppointment.id).replace(/\D/g, '');
            const patientId = currentAppointment.raw_patient_id || String(currentAppointment.patient_id).replace(/\D/g, '');

            window.location.href = `${targetPage}?id=${encodeURIComponent(patientId)}&appointment_id=${encodeURIComponent(appointmentId)}&action=new_record&source=schedule`;
        });
    }

});