
// Send email notifications to users with the 'sales' role
    foreach ($salesData as $salesPerson) {
        $email = $salesPerson['email'];
        $name = $salesPerson['name'];
        $sales = $salesPerson['sales'];
        $target = $salesPerson['target'];
        $rejects = $salesPerson['rejects'];

        $mail = new PHPMailer(true);

        try {
            // SMTP server configuration
            $mail->isSMTP();
            $mail->Host = 'cms.synaq.com'; // Replace with your SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'support@redwill.co.za'; // Replace with your SMTP username
            $mail->Password = 'rw#jmn853K7'; // Replace with your SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Email content
            $mail->setFrom('your_email@example.com', 'Sales Tracking System');
            $mail->addAddress($email, $name);
            $mail->isHTML(true);
            $mail->Subject = 'Sales Report for ' . $currentMonthName;
            $mail->Body = "
                <h1>Sales Report for $currentMonthName</h1>
                <p>Hi $name,</p>
                <p>Here is your sales report for the month:</p>
                <ul>
                    <li>Sales: $$sales</li>
                    <li>Target: $$target</li>
                    <li>Rejects: $rejects</li>
                </ul>
                <p>Keep up the good work!</p>
                <p>Regards,</p>
                <p>Sales Tracking System</p>";

            $mail->send();
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }