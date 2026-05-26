<?php
/**
 * Email sending via PHPMailer + Gmail SMTP
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/email_templates.php';
require_once __DIR__ . '/phpmailer/src/Exception.php';
require_once __DIR__ . '/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Send an HTML email via Gmail SMTP.
 */
function sendEmail(string $to, string $subject, string $body): bool
{
    if (!MAIL_ENABLED) {
        return false;
    }

    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        throw new MailException('Invalid recipient email address.');
    }

    if (MAIL_PASSWORD === 'your_app_password' || MAIL_FROM === 'your@gmail.com') {
        error_log('SMM Panel: Email not sent — configure MAIL_FROM and MAIL_PASSWORD in config.php');
        return false;
    }

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = MAIL_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = MAIL_FROM;
    $mail->Password   = MAIL_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = MAIL_PORT;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
    $mail->addAddress($to);
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $body;
    $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $body));

    $mail->send();
    return true;
}

function sendEmailSafe(string $to, string $subject, string $body): void
{
    try {
        sendEmail($to, $subject, $body);
    } catch (Throwable $e) {
        error_log('SMM Panel email failed to ' . $to . ': ' . $e->getMessage());
    }
}

function getAdminEmails(): array
{
    $db = getDB();
    $stmt = $db->query("SELECT email FROM users WHERE role = 'admin' AND status = 'active'");
    return array_filter(array_column($stmt->fetchAll(), 'email'));
}

function sendAdminEmailsSafe(string $subject, string $body): void
{
    foreach (getAdminEmails() as $email) {
        sendEmailSafe($email, $subject, $body);
    }
}

function sendTemplateEmailSafe(string $to, array $template): void
{
    sendEmailSafe($to, $template['subject'], $template['body']);
}

function sendTemplateToAdminsSafe(array $template): void
{
    sendAdminEmailsSafe($template['subject'], $template['body']);
}

/* ─── Event triggers ─── */

function mailWelcomeUser(string $email, string $username): void
{
    sendTemplateEmailSafe($email, templateWelcomeEmail($username));
}

function mailAdminNewUser(string $username, string $email): void
{
    sendTemplateToAdminsSafe(templateAdminNewUserEmail($username, $email));
}

function mailOrderPlaced(string $userEmail, string $username, array $order, string $serviceName): void
{
    sendTemplateEmailSafe($userEmail, templateOrderPlacedEmail($order, $serviceName, $username));
    sendTemplateToAdminsSafe(templateAdminNewOrderEmail($order, $serviceName, $username, $userEmail));
}

function mailOrderCompleted(string $userEmail, string $username, array $order, string $serviceName): void
{
    sendTemplateEmailSafe($userEmail, templateOrderCompletedEmail($order, $serviceName, $username));
}

function mailOrderFailed(string $userEmail, string $username, array $order, string $serviceName, bool $refunded = true): void
{
    sendTemplateEmailSafe($userEmail, templateOrderFailedEmail($order, $serviceName, $username, $refunded));
}

function mailFundsAdded(string $userEmail, string $username, float $amount, float $newBalance): void
{
    sendTemplateEmailSafe($userEmail, templateFundsAddedEmail($username, $amount, $newBalance));
}

function mailAdminPaymentReceived(string $username, string $userEmail, float $amount): void
{
    sendTemplateToAdminsSafe(templateAdminPaymentEmail($username, $userEmail, $amount));
}

function mailTicketReply(string $userEmail, string $username, string $subject, string $replyMessage, int $ticketId): void
{
    sendTemplateEmailSafe($userEmail, templateTicketReplyEmail($username, $subject, $replyMessage, $ticketId));
}

function mailAdminNewTicket(int $ticketId, string $subject, string $message, string $username, string $userEmail): void
{
    sendTemplateToAdminsSafe(templateAdminNewTicketEmail($ticketId, $subject, $message, $username, $userEmail));
}

function orderWasRefunded(int $orderId): bool
{
    $db = getDB();
    $stmt = $db->prepare("SELECT id FROM transactions WHERE order_id = ? AND type = 'refund' LIMIT 1");
    $stmt->execute([$orderId]);
    return (bool) $stmt->fetch();
}
