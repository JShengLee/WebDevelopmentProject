<?php
$visits = $visits ?? [];
$recordDetails = $recordDetails ?? [];
$services = $services ?? [];
$patient = $patient ?? [];
?>
<section class="record-card" id="visitWorkspace" hidden>

    <div class="record-tabs" role="tablist" aria-label="Patient dental record sections">
        <button class="record-tab active" id="tab-overview" data-tab="overview" role="tab" aria-selected="true" aria-controls="overview">
            <i class="fa-solid fa-folder-open"></i> Overview
        </button>
        <button class="record-tab" id="tab-examination" data-tab="examination" role="tab" aria-selected="false" aria-controls="examination">
            <i class="fa-solid fa-stethoscope"></i> Examination
        </button>
        <button class="record-tab" id="tab-tooth-chart" data-tab="tooth-chart" role="tab" aria-selected="false" aria-controls="tooth-chart">
            <i class="fa-solid fa-tooth"></i> Tooth Chart
        </button>
        <button class="record-tab" id="tab-treatment" data-tab="treatment" role="tab" aria-selected="false" aria-controls="treatment">
            <i class="fa-solid fa-notes-medical"></i> Diagnosis &amp; Treatment
        </button>
        <button class="record-tab" id="tab-prescription" data-tab="prescription" role="tab" aria-selected="false" aria-controls="prescription">
            <i class="fa-solid fa-prescription"></i> Prescription
        </button>
        <button class="record-tab" id="tab-orthodontics" data-tab="orthodontics" role="tab" aria-selected="false" aria-controls="orthodontics">
            <i class="fa-solid fa-teeth"></i> Orthodontics
        </button>
    </div>

    <!-- 1. Overview -->
    <div class="tab-panel active" id="overview" role="tabpanel" aria-labelledby="tab-overview">
        <div class="section-heading">
            <div>
                <h2>Dental Records Overview</h2>
                <p>Review the patient's previous dental visits.</p>
            </div>
        </div>

        <div class="summary-grid">
            <div class="summary-box">
                <span>Total Dental Records</span>
                <strong><?= count($visits) ?></strong>
            </div>
            <div class="summary-box">
                <span>Latest Visit</span>
                <strong><?= !empty($visits) ? escape_html($visits[0]['date']) : 'No visits yet' ?></strong>
            </div>
            <div class="summary-box">
                <span>Current Treatment</span>
                <strong><?= escape_html($patient['treatment']) ?></strong>
            </div>
        </div>

        <h3 class="subsection-title">Recent Dental History</h3>

        <div class="table-responsive">
            <table class="record-table">
                <thead>
                    <tr>
                        <th>Record Date</th>
                        <th>Diagnosis</th>
                        <th>Clinical Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($visits)): ?>
                        <?php foreach (array_slice($visits, 0, 4) as $visit): ?>
                            <?php $record = $recordDetails[$visit['id']]; ?>
                            <tr>
                                <td><?= escape_html($visit['date']) ?></td>
                                <td><?= escape_html($record['diagnosis'] ?: 'Not recorded') ?></td>
                                <td><?= escape_html($record['clinicalNotes'] ?: 'No notes recorded') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3">No dental history available.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 2. Dental Examination -->
    <div class="tab-panel" id="examination" role="tabpanel" aria-labelledby="tab-examination" hidden>
        <div class="section-heading">
            <div>
                <h2>Dental Examination</h2>
                <p>Record findings from the patient's examination.</p>
            </div>
        </div>

        <form class="record-form" id="examinationForm">
            <div class="form-group">
                <label for="examDate">Examination Date</label>
                <input type="date" id="examDate" max="<?= escape_html(date('Y-m-d')) ?>" required>
            </div>

            <div class="form-group">
                <label for="oralHygiene">Oral Hygiene</label>
                <select id="oralHygiene" required>
                    <option value="">Select finding</option>
                    <option>Good</option>
                    <option>Fair</option>
                    <option>Needs Improvement</option>
                </select>
            </div>

            <div class="form-group">
                <label for="gumCondition">Gum Condition</label>
                <select id="gumCondition" required>
                    <option value="">Select finding</option>
                    <option>Healthy</option>
                    <option>Inflammation Observed</option>
                    <option>Further Assessment Required</option>
                </select>
            </div>

            <div class="form-group full-width">
                <label for="examNotes">Clinical Notes</label>
                <textarea id="examNotes" rows="4" placeholder="Enter clinical findings..."></textarea>
            </div>

            <div class="form-actions full-width">
                <button type="reset" class="secondary-button">Clear</button>
                <button type="submit" class="primary-button">Save Examination</button>
            </div>
        </form>
    </div>

    <!-- 3. Interactive Tooth Chart -->
    <div class="tab-panel" id="tooth-chart" role="tabpanel" aria-labelledby="tab-tooth-chart" hidden>
        <div class="section-heading">
            <div>
                <h2>Interactive Tooth Chart</h2>
                <p>Select a tooth to assign a condition and add clinical notes.</p>
            </div>
        </div>

        <div class="tooth-legend">
            <span><i class="legend-dot healthy"></i> Healthy</span>
            <span><i class="legend-dot cavity"></i> Cavity</span>
            <span><i class="legend-dot treated"></i> Treated</span>
            <span><i class="legend-dot others"></i> Others</span>
        </div>

        <form class="record-form" id="toothChartForm">
            <div class="tooth-chart">
                <h3>Upper Teeth</h3>
                <div class="tooth-row" id="upperTeeth"></div>

                <div class="chart-divider"></div>

                <h3>Lower Teeth</h3>
                <div class="tooth-row" id="lowerTeeth"></div>
            </div>

            <div class="tooth-editor">
                <div>
                    <label for="selectedTooth">Selected Tooth</label>
                    <input id="selectedTooth" type="text" value="None selected" readonly>
                </div>

                <div>
                    <label for="toothCondition">Condition</label>
                    <select id="toothCondition">
                        <option value="healthy">Healthy</option>
                        <option value="cavity">Cavity</option>
                        <option value="treated">Treated</option>
                        <option value="others">Others</option>
                    </select>
                </div>

                <!-- Added custom input field for "Others" -->
                <div id="customConditionGroup" style="display: none;">
                    <label for="customToothCondition">Specify Condition</label>
                    <input type="text" id="customToothCondition" placeholder="Enter condition...">
                </div>

                <button type="button" id="applyToothCondition" class="primary-button">
                    Apply Condition
                </button>

                <div class="full-width">
                    <label for="toothNotes">Tooth Notes</label>
                    <textarea id="toothNotes" rows="3" placeholder="Enter notes for the selected tooth..."></textarea>
                </div>
            </div>

            <div class="form-actions full-width" style="margin-top: 18px;">
                <button type="button" id="clearToothChart" class="secondary-button">Clear</button>
                <button type="submit" class="primary-button">Save Chart</button>
            </div>
        </form>
    </div>

    <!-- 4. Diagnosis & Treatment -->
    <div class="tab-panel" id="treatment" role="tabpanel" aria-labelledby="tab-treatment" hidden>
        <div class="section-heading">
            <div>
                <h2>Diagnosis &amp; Treatment</h2>
                <p>Record the diagnosis and treatment associated with a dental visit.</p>
            </div>
        </div>

        <form class="record-form" id="treatmentForm">
            <div class="form-group">
                <label for="diagnosisDate">Treatment Date</label>
                <input type="date" id="diagnosisDate" max="<?= escape_html(date('Y-m-d')) ?>" required>
            </div>

            <div class="form-group">
                <label for="diagnosis">Diagnosis</label>
                <input type="text" id="diagnosis" placeholder="Enter diagnosis" required>
            </div>

            <div class="form-group full-width">
                <label for="treatmentName">Treatment Name</label>
                <select id="treatmentName" required>
                    <option value="">Select treatment service</option>
                    <?php foreach ($services as $service): ?>
                        <option value="<?= escape_html($service['ServiceName']) ?>">
                            <?= escape_html($service['ServiceName']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group full-width">
                <label for="treatmentDetails">Treatment Details</label>
                <textarea id="treatmentDetails" rows="4" placeholder="Enter treatment details..."></textarea>
            </div>

            <div class="form-actions full-width">
                <button type="reset" class="secondary-button">Clear</button>
                <button type="submit" class="primary-button">Save Treatment</button>
            </div>
        </form>
    </div>

    <!-- 5. Prescription -->
    <div class="tab-panel" id="prescription" role="tabpanel" aria-labelledby="tab-prescription" hidden>
        <div class="section-heading">
            <div>
                <h2>Prescription Management</h2>
                <p>Record medication prescribed for a dental record.</p>
            </div>
        </div>

        <form class="record-form" id="prescriptionForm">
            <div class="form-group full-width">
                <label for="prescriptionRecordDisplay">Dental Record</label>
                <input type="text" id="prescriptionRecordDisplay" value="Select or start a visit" readonly disabled>
            </div>

            <div class="form-group">
                <label for="medicationName">Medication Name</label>
                <input type="text" id="medicationName" placeholder="Enter medication" required>
            </div>

            <div class="form-group">
                <label for="dosage">Dosage</label>
                <input type="text" id="dosage" placeholder="Enter prescribed dosage">
            </div>

            <div class="form-group full-width">
                <label for="prescriptionInstructions">Instructions</label>
                <textarea id="prescriptionInstructions" rows="4" placeholder="Enter the dentist's instructions..."></textarea>
            </div>

            <div class="form-actions full-width">
                <button type="reset" class="secondary-button">Clear</button>
                <button type="submit" class="primary-button">Save Prescription</button>
            </div>
        </form>
    </div>

    <!-- 6. Orthodontic Treatment -->
    <div class="tab-panel" id="orthodontics" role="tabpanel" aria-labelledby="tab-orthodontics" hidden>
        <div class="section-heading">
            <div>
                <h2>Orthodontic Treatment</h2>
                <p>Track braces treatment and adjustment progress.</p>
            </div>
        </div>

        <form class="record-form" id="orthodonticsForm">
            <div class="form-group">
                <label for="treatmentType">Treatment Type</label>
                <input type="text" id="treatmentType" placeholder="e.g. Metal braces" required>
            </div>

            <div class="form-group">
                <label for="startDate">Start Date</label>
                <input type="date" id="startDate" max="<?= escape_html(date('Y-m-d')) ?>" required>
            </div>

            <div class="form-group">
                <label for="currentStage">Current Stage</label>
                <select id="currentStage" required>
                    <option value="">Select stage</option>
                    <option>Initial Consultation</option>
                    <option>Active Treatment</option>
                    <option>Adjustment</option>
                    <option>Retention</option>
                    <option>Completed</option>
                </select>
            </div>

            <div class="form-group">
                <label for="nextAdjustment">Next Adjustment Date</label>
                <input type="date" id="nextAdjustment">
            </div>

            <div class="form-group full-width">
                <label for="progressNotes">Progress Notes</label>
                <textarea id="progressNotes" rows="4" placeholder="Enter progress notes..."></textarea>
            </div>

            <div class="form-actions full-width">
                <button type="reset" class="secondary-button">Clear</button>
                <button type="submit" class="primary-button">Save Orthodontic Record</button>
            </div>
        </form>
    </div>

</section>