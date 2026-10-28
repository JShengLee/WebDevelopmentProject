<?php
require_once __DIR__ . '/../includes/patient_bootstrap.php';

date_default_timezone_set("Asia/Kuala_Lumpur");

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "sendMessage"){
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $subject = $_POST["subject"];
    $message = $_POST["message"];
    $timestamp = date("Y-m-d H:i:s");
    try{
        $sql = "INSERT INTO message (FullName, Email, Subject, MessageContent, ReadStatus, MessageTimeStamp)
                VALUES (?, ?, ?, ?, 'Unread', ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $name,
            $email,
            $subject,
            $message,
            $timestamp
        ]);

        echo"<script>
                    alert('Your message has been successfully submitted!');
                    window.location.href = window.location.pathname;    
                </script>";
    } catch (PDOException $e){
        error_log($e->getMessage());

        echo"<script>
                alert('Unable to submit message.');
            </script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/x-icon" href="../Logo.png">
        <title>Contact Us | BrightSmile Dental Clinic</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="../Styling/contact.css">
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
                <div class="page-content">

                    <section class="page-header">
                        <h1>
                            Contact BrightSmile
                        </h1>

                        <p>
                            Have a question? Find our clinic information
                            or send us a message.
                        </p>
                    </section>

                    <!-- Contact grid -->
                    <section class="contact-grid">
                        <!-- Clinic information -->
                        <div class="contact-information">

                            <div class="info-card">
                                <div class="info-icon">
                                    ◷
                                </div>

                                <div>
                                    <h3>
                                        Operating Hours
                                    </h3>

                                    <p>
                                        Monday – Friday
                                    </p>

                                    <strong>
                                        9:00 AM – 6:00 PM
                                    </strong>

                                    <p class="extra-hours">
                                        Saturday
                                    </p>

                                    <strong>
                                        9:00 AM – 1:00 PM
                                    </strong>

                                    <p class="closed">
                                        Sunday & Public Holidays · Closed
                                    </p>
                                </div>
                            </div>

                            <div class="info-card">
                                <div class="info-icon">
                                    ☎
                                </div>

                                <div>
                                    <h3>
                                        Phone
                                    </h3>

                                    <p>
                                        Clinic reception
                                    </p>

                                    <strong>
                                        +60 4-123 4567
                                    </strong>
                                </div>
                            </div>

                            <div class="info-card">
                                <div class="info-icon">
                                    @
                                </div>

                                <div>
                                    <h3>
                                        Email
                                    </h3>

                                    <p>
                                        General enquiries
                                    </p>

                                    <strong>
                                        hello@brightsmile.com
                                    </strong>
                                </div>
                            </div>

                            <div class="info-card">
                                <div class="info-icon">
                                    ◉
                                </div>

                                <div>
                                    <h3>
                                        Clinic Location
                                    </h3>

                                    <p>
                                        BrightSmile Dental Clinic
                                    </p>

                                    <strong>
                                        Penang, Malaysia
                                    </strong>
                                </div>
                            </div>
                        </div>

                        <!-- Contact form -->
                        <div class="contact-form-card">
                            <div class="form-heading">
                                <h2>
                                    Send Us a Message
                                </h2>

                                <p>
                                    Fill in the form below and our clinic
                                    team will get back to you.
                                </p>
                            </div>

                            <form
                                class="contact-form"
                                action="#"
                                method="post"
                            >
                                <input type="hidden" name="action" value="sendMessage" />

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="name">
                                            Full Name
                                        </label>

                                        <input
                                            type="text"
                                            id="name"
                                            name="name"
                                            placeholder="Enter your name"
                                            required
                                        >
                                    </div>

                                    <div class="form-group">
                                        <label for="email">
                                            Email Address
                                        </label>

                                        <input
                                            type="email"
                                            id="email"
                                            name="email"
                                            placeholder="Enter your email"
                                            required
                                        >
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="subject">
                                        Subject
                                    </label>

                                    <select
                                        id="subject"
                                        name="subject"
                                        required
                                    >

                                        <option value="" disabled selected>
                                            Select a subject
                                        </option>

                                        <option value="appointment">
                                            Appointment enquiry
                                        </option>

                                        <option value="treatment">
                                            Treatment enquiry
                                        </option>

                                        <option value="services">
                                            Service enquiry
                                        </option>

                                        <option value="other">
                                            Other
                                        </option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="message">
                                        Message
                                    </label>

                                    <textarea
                                        id="message"
                                        name="message"
                                        rows="6"
                                        placeholder="Write your message here..."
                                        required
                                    ></textarea>
                                </div>

                                <button
                                    type="submit"
                                    class="send-button"
                                >
                                    Send Message
                                </button>
                            </form>
                        </div>
                    </section>

                    <!-- Response notice -->
                    <div class="response-note">
                        <span>ⓘ</span>

                        <p>
                            For urgent dental concerns, please contact
                            the clinic directly by phone rather than
                            using this form.
                        </p>
                    </div>
                </div>
            </main>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://kit.fontawesome.com/f3ce044c74.js" crossorigin="anonymous"></script>
    </body>
</html>