document.addEventListener("DOMContentLoaded", function () {

    const urlParams = new URLSearchParams(window.location.search);
    const rawPatientID = urlParams.get('patient_id') || urlParams.get('id');
    const patientID = rawPatientID ? parseInt(rawPatientID.replace(/\D/g, ''), 10) : null;

    const overviewTab = document.querySelector('.record-tab[data-tab="overview"]');
    const overviewPanel = document.getElementById("overview");

    const visitWorkspace = document.getElementById("visitWorkspace");
    const visitHistory = document.querySelector(".visit-section");
    const activeVisitBanner = document.getElementById("activeVisitBanner");

    const activeVisitTitle = document.getElementById("activeVisitTitle");
    const activeVisitDescription = document.getElementById("activeVisitDescription");

    const startNewVisitButton = document.getElementById("startNewVisit");
    const changeVisitButton = document.getElementById("changeVisit");

    let activeVisitID = null;
    let currentTreatmentID = null;

    function populateServiceDropdown() {
        const treatmentSelect = document.getElementById("treatmentName");
        if (!treatmentSelect || typeof availableServices === "undefined") return;

        treatmentSelect.innerHTML = '<option value="">Select Treatment / Service</option>';
        availableServices.forEach(service => {
            const option = document.createElement("option");
            option.value = service.ServiceName;
            option.textContent = service.ServiceName;
            treatmentSelect.appendChild(option);
        });
    }
    
    // Helper function for AJAX calls
    async function sendRequest(payload) {
        payload.patientID = patientID;
        
        const apptId = urlParams.get('appointment_id');
        if (payload.action === "create_visit" && apptId) {
            payload.appointmentID = parseInt(String(apptId).replace(/\D/g, ''), 10);
        }

        try {
            const response = await fetch("save_patient_record.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            });
            return await response.json();
        } catch (error) {
            console.error("Save Error:", error);
            return { success: false, message: "Network error occurred." };
        }
    }

    let activeVisitContextRequest = sendRequest({ action: "initialize_record_page" });

    // Dynamic show/hide handler for custom condition input box
    const toothConditionSelect = document.getElementById("toothCondition");
    const customConditionGroup = document.getElementById("customConditionGroup");
    const customConditionInput = document.getElementById("customToothCondition");

    if (toothConditionSelect && customConditionGroup) {
        toothConditionSelect.addEventListener("change", function () {
            if (this.value === "others") {
                customConditionGroup.style.display = "block";
                if (customConditionInput) customConditionInput.focus();
            } else {
                customConditionGroup.style.display = "none";
                if (customConditionInput) customConditionInput.value = "";
            }
        });
    }

    // Reset all forms
    function resetVisitForms() {
        document.querySelectorAll("form").forEach(form => form.reset());

        Object.keys(toothStates).forEach(key => delete toothStates[key]);
        Object.keys(toothNotesMap).forEach(key => delete toothNotesMap[key]);

        document.querySelectorAll(".tooth-button").forEach(button => {
            button.classList.remove("healthy", "cavity", "treated", "others", "selected");
            button.setAttribute("aria-pressed", "false");
        });

        selectedTooth = null;

        const selectedToothField = document.getElementById("selectedTooth");
        if (selectedToothField) selectedToothField.value = "None selected";
        
        if (toothConditionSelect) toothConditionSelect.value = "healthy";
        if (customConditionGroup) customConditionGroup.style.display = "none";
        if (customConditionInput) customConditionInput.value = "";
    }

    function setFieldValue(id, value) {
        const field = document.getElementById(id);
        if (!field) return;
        const fieldValue = value == null ? "" : String(value);

        if (field.tagName === "SELECT" && fieldValue !== "") {
            const optionExists = Array.from(field.options).some(opt => opt.value === fieldValue);
            if (!optionExists) {
                const option = document.createElement("option");
                option.value = fieldValue;
                option.textContent = fieldValue;
                field.appendChild(option);
            }
        }
        field.value = fieldValue;
    }

    function setVisitDateFieldsLocked(locked) {
        ["examDate", "diagnosisDate"].forEach(id => {
            const field = document.getElementById(id);
            if (!field) return;
            field.disabled = locked;
            field.title = locked
                ? "This saved date cannot be changed when editing a dental record."
                : "";
            field.setAttribute("aria-disabled", String(locked));
        });
    }

    function fillNewVisitDates() {
        const today = document.getElementById("examDate").max;
        setVisitDateFieldsLocked(false);
        setFieldValue("examDate", today);
        setFieldValue("diagnosisDate", today);
        setFieldValue("startDate", today);
    }

    function formatInputDate(value) {
        if (!value) return "";
        return String(value).substring(0, 10);
    }

    function loadVisitDetails(id) {
        const record = recordDetails[id];
        if (!record) return;

        resetVisitForms();

        setFieldValue("examDate", formatInputDate(record.date));
        setFieldValue("oralHygiene", record.oralHygiene);
        setFieldValue("gumCondition", record.gumCondition);
        setFieldValue("examNotes", record.clinicalNotes);

        (record.teeth || []).forEach(tooth => {
            const number = String(tooth.number);
            const rawCondition = String(tooth.condition || "").trim();
            const condLower = rawCondition.toLowerCase();
            const notes = tooth.notes || "";

            let chartClass = "healthy";

            if (condLower === "healthy") {
                chartClass = "healthy";
            } else if (condLower.includes("cavity")) {
                chartClass = "cavity";
            } else if (condLower.includes("treated") || condLower.includes("fill")) {
                chartClass = "treated";
            } else {
                chartClass = "others";
            }

            toothStates[number] = rawCondition;
            toothNotesMap[number] = notes;

            const toothButton = document.querySelector(`.tooth-button[data-tooth="${number}"]`);
            if (toothButton) {
                toothButton.classList.remove("healthy", "cavity", "treated", "others");
                toothButton.classList.add(chartClass);
            }
        });

        const treatments = record.treatments || [];
        const treatment = treatments.length > 0 ? treatments[0] : null;
        currentTreatmentID = treatment?.id || null; // Save Treatment ID if existing

        setFieldValue("diagnosisDate", formatInputDate(treatment?.date || record.date));
        setFieldValue("diagnosis", record.diagnosis);
        setFieldValue("treatmentName", treatment?.name || "");
        setFieldValue("treatmentDetails", treatment?.details || "");

        const prescriptions = record.prescriptions || [];
        const prescription = prescriptions.length > 0 ? prescriptions[0] : null;

        setFieldValue("medicationName", prescription?.medication || "");
        setFieldValue("dosage", prescription?.dosage || "");
        setFieldValue("prescriptionInstructions", prescription?.instructions || "");

        const orthodonticRecord = orthodonticRecords.length > 0 ? orthodonticRecords[0] : null;
        setFieldValue("treatmentType", orthodonticRecord?.TreatmentType || "");
        const orthoStartDate = orthodonticRecord ? orthodonticRecord.StartDate : record.date;
        setFieldValue("startDate", formatInputDate(orthoStartDate));
        setFieldValue("currentStage", orthodonticRecord?.CurrentStage || "");
        setFieldValue("nextAdjustment", formatInputDate(orthodonticRecord?.NextAdjustmentDate));
        setFieldValue("progressNotes", orthodonticRecord?.ProgressNotes || "");
        setVisitDateFieldsLocked(true);
    }

    async function openVisit(id, date, treatment, isNewVisit = false, appointmentTreatmentName = "") {
        await activeVisitContextRequest;
        const activation = await sendRequest({
            action: "activate_visit",
            recordID: id
        });
        if (!activation.success) {
            alert(activation.message || "Unable to open this dental record.");
            return;
        }

        activeVisitID = id;

        if (isNewVisit) {
            resetVisitForms();
            currentTreatmentID = null; // Reset treatment ID for new visit
            fillNewVisitDates();
            setFieldValue("prescriptionRecordDisplay", `Record #${id} — ${date}`);
            if (appointmentTreatmentName) {
                setFieldValue("treatmentName", appointmentTreatmentName);
            }
        } else {
            loadVisitDetails(id);
            setFieldValue("prescriptionRecordDisplay", `Record #${id} — ${date}`);
        }

        activeVisitTitle.textContent = "Dental Visit";
        activeVisitDescription.textContent = "Record #" + id + " · " + date + " · " + treatment;

        activeVisitBanner.hidden = false;
        visitWorkspace.hidden = false;

        if (overviewTab && overviewPanel) {
            overviewTab.hidden = isNewVisit;
            overviewPanel.hidden = isNewVisit;

            if (isNewVisit) {
                overviewPanel.classList.remove("active");
                overviewTab.classList.remove("active");
                overviewTab.setAttribute("aria-selected", "false");
            }
        }

        if (isNewVisit) {
            document.querySelector('.record-tab[data-tab="examination"]')?.click();
        } else {
            overviewTab?.click();
        }

        activeVisitBanner.scrollIntoView({ behavior: "smooth", block: "start" });
    }

    document.querySelectorAll(".view-visit-button").forEach(button => {
        button.addEventListener("click", async function () {
            await openVisit(button.dataset.visitId, button.dataset.visitDate, button.dataset.visitTreatment);
        });
    });

    // 1. START NEW VISIT (Create database record via PHP)
    if (startNewVisitButton) {
        startNewVisitButton.addEventListener("click", async function () {
            await activeVisitContextRequest;
            const res = await sendRequest({ action: "create_visit" });
            if (res.success) {
                await openVisit(res.recordID, res.recordDate, "New dental visit", true);
                alert("New visit created successfully! Record ID: #" + res.recordID);
            } else {
                alert("Error creating new visit: " + res.message);
            }
        });
    }

    if (changeVisitButton) {
        changeVisitButton.addEventListener("click", function () {
            activeVisitID = null;
            activeVisitContextRequest = sendRequest({ action: "clear_active_visit" });
            activeVisitBanner.hidden = true;
            visitWorkspace.hidden = true;
            if (visitHistory) visitHistory.scrollIntoView({ behavior: "smooth", block: "start" });
        });
    }

    // Tabs setup
    const tabs = document.querySelectorAll(".record-tab");
    const panels = document.querySelectorAll(".tab-panel");

    tabs.forEach(tab => {
        tab.addEventListener("click", function () {
            const targetID = tab.dataset.tab;
            tabs.forEach(item => {
                item.classList.remove("active");
                item.setAttribute("aria-selected", "false");
            });
            panels.forEach(panel => {
                panel.classList.remove("active");
                panel.hidden = true;
            });
            tab.classList.add("active");
            tab.setAttribute("aria-selected", "true");
            const targetPanel = document.getElementById(targetID);
            if (targetPanel) {
                targetPanel.classList.add("active");
                targetPanel.hidden = false;
            }
        });
    });

    // Interactive Tooth Chart Setup
    const upperTeeth = document.getElementById("upperTeeth");
    const lowerTeeth = document.getElementById("lowerTeeth");
    let selectedTooth = null;
    const toothStates = {};
    const toothNotesMap = {};

    const upperNumbers = [18, 17, 16, 15, 14, 13, 12, 11, 21, 22, 23, 24, 25, 26, 27, 28];
    const lowerNumbers = [48, 47, 46, 45, 44, 43, 42, 41, 31, 32, 33, 34, 35, 36, 37, 38];

    function createMidlineDivider() {
        const divider = document.createElement("div");
        divider.className = "tooth-midline-divider";
        divider.title = "Midline (Left / Right)";
        return divider;
    }

    function createTooth(number, container) {
        const button = document.createElement("button");
        button.type = "button";
        button.className = "tooth-button";
        button.dataset.tooth = number;
        button.setAttribute("aria-pressed", "false");
        button.innerHTML = `<span class="tooth-symbol"><i class="fa-solid fa-tooth"></i></span><span>${number}</span>`;

        button.addEventListener("click", function () {
            selectedTooth = String(number);
            document.getElementById("selectedTooth").value = "Tooth " + number;

            const currentCond = toothStates[number] || "healthy";
            const condLower = currentCond.toLowerCase();

            if (["healthy", "cavity", "treated"].includes(condLower)) {
                if (toothConditionSelect) toothConditionSelect.value = condLower;
                if (customConditionGroup) customConditionGroup.style.display = "none";
                if (customConditionInput) customConditionInput.value = "";
            } else {
                if (toothConditionSelect) toothConditionSelect.value = "others";
                if (customConditionGroup) customConditionGroup.style.display = "block";
                if (customConditionInput) customConditionInput.value = currentCond;
            }

            document.getElementById("toothNotes").value = toothNotesMap[number] || "";

            document.querySelectorAll(".tooth-button").forEach(item => {
                item.classList.remove("selected");
                item.setAttribute("aria-pressed", "false");
            });

            button.classList.add("selected");
            button.setAttribute("aria-pressed", "true");
        });

        container.appendChild(button);
    }

    if (upperTeeth && lowerTeeth) {
        upperTeeth.innerHTML = "";
        lowerTeeth.innerHTML = "";

        upperNumbers.forEach(number => {
            createTooth(number, upperTeeth);
            if (number === 11) upperTeeth.appendChild(createMidlineDivider());
        });

        lowerNumbers.forEach(number => {
            createTooth(number, lowerTeeth);
            if (number === 41) lowerTeeth.appendChild(createMidlineDivider());
        });
    }

    const applyButton = document.getElementById("applyToothCondition");
    const toothNotesInput = document.getElementById("toothNotes");

    if (applyButton) {
        applyButton.addEventListener("click", function () {
            if (!selectedTooth) {
                alert("Please select a tooth first.");
                return;
            }

            let conditionVal = toothConditionSelect ? toothConditionSelect.value : "healthy";
            let finalConditionText = conditionVal;

            if (conditionVal === "others") {
                const customText = customConditionInput ? customConditionInput.value.trim() : "";
                if (!customText) {
                    alert("Please enter the custom condition.");
                    if (customConditionInput) customConditionInput.focus();
                    return;
                }
                finalConditionText = customText;
            }

            toothStates[selectedTooth] = finalConditionText;
            toothNotesMap[selectedTooth] = toothNotesInput ? toothNotesInput.value.trim() : "";

            const toothButton = document.querySelector(`.tooth-button[data-tooth="${selectedTooth}"]`);
            if (toothButton) {
                toothButton.classList.remove("healthy", "cavity", "treated", "others");
                toothButton.classList.add(conditionVal);
            }

            alert(`Tooth ${selectedTooth} updated on chart.`);
        });
    }

    // 2. Examination Form
    const examinationForm = document.getElementById("examinationForm");
    if (examinationForm) {
        examinationForm.addEventListener("submit", async function (e) {
            e.preventDefault();
            if (!activeVisitID) return alert("Select or start a visit first.");

            const res = await sendRequest({
                action: "save_examination",
                recordID: activeVisitID,
                examDate: document.getElementById("examDate").value,
                oralHygiene: document.getElementById("oralHygiene").value,
                gumCondition: document.getElementById("gumCondition").value,
                examNotes: document.getElementById("examNotes").value
            });

            alert(res.message);
            // REMOVED location.reload() here so the tab stays open
        });
    }

    // 3. Tooth Chart Form
    const toothChartForm = document.getElementById("toothChartForm");
    if (toothChartForm) {
        toothChartForm.addEventListener("submit", async function (e) {
            e.preventDefault();
            if (!activeVisitID) return alert("Select or start a visit first.");

            const teethPayload = Object.keys(toothStates).map(num => ({
                number: num,
                condition: toothStates[num],
                notes: toothNotesMap[num] || ""
            }));

            const res = await sendRequest({
                action: "save_tooth_chart",
                recordID: activeVisitID,
                teeth: teethPayload
            });

            alert(res.message);
            // REMOVED location.reload() here so the tab stays open
        });
    }

    // 4. Treatment Form
    const treatmentForm = document.getElementById("treatmentForm");
    if (treatmentForm) {
        treatmentForm.addEventListener("submit", async function (e) {
            e.preventDefault();
            if (!activeVisitID) return alert("Select or start a visit first.");

            const res = await sendRequest({
                action: "save_treatment",
                recordID: activeVisitID,
                treatmentID: currentTreatmentID, // Pass active treatment ID
                treatmentDate: document.getElementById("diagnosisDate").value,
                diagnosis: document.getElementById("diagnosis").value,
                treatmentName: document.getElementById("treatmentName").value,
                treatmentDetails: document.getElementById("treatmentDetails").value
            });

            if (res.success && res.treatmentID) {
                currentTreatmentID = res.treatmentID; // Update state with saved TreatmentID
            }

            alert(res.message);
        });
    }

    // 5. Prescription Form
    const prescriptionForm = document.getElementById("prescriptionForm");
    if (prescriptionForm) {
        prescriptionForm.addEventListener("submit", async function (e) {
            e.preventDefault();
            if (!activeVisitID) return alert("Select or start a visit first.");

            const res = await sendRequest({
                action: "save_prescription",
                medicationName: document.getElementById("medicationName").value,
                dosage: document.getElementById("dosage").value,
                instructions: document.getElementById("prescriptionInstructions").value
            });

            alert(res.message);
            // REMOVED location.reload() here so the tab stays open
        });
    }

    // 6. Orthodontics Form
    const orthodonticsForm = document.getElementById("orthodonticsForm");
    if (orthodonticsForm) {
        orthodonticsForm.addEventListener("submit", async function (e) {
            e.preventDefault();

            const res = await sendRequest({
                action: "save_orthodontics",
                recordID: activeVisitID,
                treatmentType: document.getElementById("treatmentType").value,
                startDate: document.getElementById("startDate").value,
                currentStage: document.getElementById("currentStage").value,
                nextAdjustment: document.getElementById("nextAdjustment").value,
                progressNotes: document.getElementById("progressNotes").value
            });

            alert(res.message);
        });
    }

    // Automatically start new visit if directed from Schedule page
    const actionParam = urlParams.get('action');
    const appointmentIDParam = urlParams.get('appointment_id');

    if (actionParam === 'new_record') {
        (async function () {
            await activeVisitContextRequest;
            const res = await sendRequest({ 
                action: "create_visit",
                appointmentID: appointmentIDParam 
            });

            if (res.success) {
                const scheduleStart = urlParams.get('source') === 'schedule' && appointmentIDParam;
                await openVisit(
                    res.recordID,
                    res.recordDate,
                    "New dental visit",
                    true,
                    scheduleStart ? res.appointmentTreatmentName : ""
                );
            } else {
                alert("Error creating visit record: " + res.message);
            }
        })();
    }

    // REAL-TIME RECORD SEARCH AND DATE FILTER
    const searchRecordInput = document.getElementById("searchRecordID");
    const filterDateInput = document.getElementById("filterVisitDate");
    const clearSearchBtn = document.getElementById("clearSearchBtn");
    const clearDateBtn = document.getElementById("clearDateBtn");
    const visitCards = document.querySelectorAll(".visit-card");
    const noFilterResults = document.getElementById("noFilterResults");
    const visitCountDisplay = document.getElementById("visitCountDisplay");

    function applyVisitFilters() {
        if (!visitCards.length) return;

        const searchValue = searchRecordInput ? searchRecordInput.value.trim().toLowerCase() : "";
        const dateValue = filterDateInput ? filterDateInput.value : "";

        // Toggle clear button visibility
        if (clearSearchBtn) clearSearchBtn.hidden = !searchValue;
        if (clearDateBtn) clearDateBtn.hidden = !dateValue;

        let visibleCount = 0;

        visitCards.forEach(card => {
            const visitID = String(card.dataset.visitId || "").toLowerCase();
            const visitRawDate = String(card.dataset.visitRawDate || "").substring(0, 10);

            // Match Record ID (ignoring '#' if typed) and Date filter
            const matchesID = !searchValue || visitID.includes(searchValue.replace('#', ''));
            const matchesDate = !dateValue || visitRawDate === dateValue;

            if (matchesID && matchesDate) {
                card.style.display = "";
                visibleCount++;
            } else {
                card.style.display = "none";
            }
        });

        // Update count display and toggle empty results card
        if (visitCountDisplay) {
            visitCountDisplay.textContent = `${visibleCount} ${visibleCount === 1 ? 'Visit' : 'Visits'}`;
        }

        if (noFilterResults) {
            noFilterResults.hidden = visibleCount > 0;
        }
    }

    if (searchRecordInput) {
        searchRecordInput.addEventListener("input", applyVisitFilters);
    }

    if (filterDateInput) {
        filterDateInput.addEventListener("change", applyVisitFilters);
    }

    if (clearSearchBtn) {
        clearSearchBtn.addEventListener("click", function () {
            searchRecordInput.value = "";
            applyVisitFilters();
        });
    }

    if (clearDateBtn) {
        clearDateBtn.addEventListener("click", function () {
            filterDateInput.value = "";
            applyVisitFilters();
        });
    }

});