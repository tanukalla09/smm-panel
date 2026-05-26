<?php
/**
 * HTML email templates for SMM Panel
 */

function emailEscape(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function emailLayout(string $heading, string $contentHtml, ?string $buttonText = null, ?string $buttonUrl = null): string
{
    $siteName = emailEscape(SITE_NAME);
    $year = date('Y');
    $buttonHtml = '';

    if ($buttonText && $buttonUrl) {
        $btn = emailEscape($buttonText);
        $url = emailEscape($buttonUrl);
        $buttonHtml = <<<HTML
              <table cellpadding="0" cellspacing="0" style="margin:28px 0 0;">
                <tr>
                  <td style="border-radius:8px;background-color:#4F46E5;">
                    <a href="{$url}" target="_blank" style="display:inline-block;padding:14px 28px;font-size:14px;font-weight:600;color:#ffffff;text-decoration:none;border-radius:8px;">{$btn}</a>
                  </td>
                </tr>
              </table>
HTML;
    }

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{$heading}</title>
</head>
<body style="margin:0;padding:0;background-color:#F5F7FA;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;-webkit-font-smoothing:antialiased;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#F5F7FA;padding:40px 16px;">
    <tr>
      <td align="center">
        <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(15,23,42,0.08);">
          <tr>
            <td style="background:linear-gradient(135deg,#4F46E5 0%,#6366F1 100%);padding:28px 40px;text-align:center;">
              <div style="font-size:22px;font-weight:800;color:#ffffff;letter-spacing:-0.3px;line-height:1.3;">
                <span style="font-size:26px;vertical-align:middle;">⚡</span>
                <span style="vertical-align:middle;"> {$siteName}</span>
              </div>
            </td>
          </tr>
          <tr>
            <td style="padding:36px 40px 32px;">
              <h1 style="margin:0 0 20px;font-size:22px;font-weight:700;color:#111827;line-height:1.3;">{$heading}</h1>
              {$contentHtml}
              {$buttonHtml}
            </td>
          </tr>
          <tr>
            <td style="background:#F9FAFB;padding:22px 40px;border-top:1px solid #E5E7EB;text-align:center;">
              <p style="margin:0 0 6px;font-size:12px;color:#9CA3AF;line-height:1.5;">
                You received this email because you have an account on {$siteName}.
              </p>
              <p style="margin:0;font-size:12px;color:#9CA3AF;">
                &copy; {$year} {$siteName}. All rights reserved.
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
}

function emailParagraph(string $text): string
{
    return '<p style="margin:0 0 16px;font-size:15px;line-height:1.65;color:#4B5563;">' . $text . '</p>';
}

function emailDetailTable(array $rows): string
{
    $html = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:20px 0;background:#F9FAFB;border-radius:8px;border:1px solid #E5E7EB;">';
    foreach ($rows as $label => $value) {
        $html .= '<tr>';
        $html .= '<td style="padding:12px 16px;font-size:13px;color:#6B7280;border-bottom:1px solid #E5E7EB;width:38%;">' . emailEscape((string) $label) . '</td>';
        $html .= '<td style="padding:12px 16px;font-size:13px;font-weight:600;color:#111827;border-bottom:1px solid #E5E7EB;">' . $value . '</td>';
        $html .= '</tr>';
    }
    $html .= '</table>';
    return $html;
}

function emailStepsList(array $steps): string
{
    $html = '<ol style="margin:0 0 16px;padding-left:20px;color:#4B5563;font-size:15px;line-height:1.8;">';
    foreach ($steps as $step) {
        $html .= '<li style="margin-bottom:6px;">' . $step . '</li>';
    }
    $html .= '</ol>';
    return $html;
}

/* ─── User email templates ─── */

function templateWelcomeEmail(string $username): array
{
    $name = emailEscape($username);
    $content = emailParagraph("Hi <strong>{$name}</strong>, welcome aboard! We're excited to have you on " . emailEscape(SITE_NAME) . ".");
    $content .= emailParagraph('Your account is ready. Here\'s how to get started:');
    $content .= emailStepsList([
        '<strong>Add funds</strong> to your wallet via secure Stripe checkout',
        '<strong>Browse services</strong> across Instagram, YouTube, TikTok &amp; more',
        '<strong>Place your first order</strong> — delivery starts automatically',
    ]);
    $content .= emailParagraph('If you need help, visit our Help Center or open a support ticket anytime.');

    return [
        'subject' => "Welcome to " . SITE_NAME . ", {$username}! 🎉",
        'body'    => emailLayout('Welcome to ' . SITE_NAME . '! 🎉', $content, 'Go to Dashboard', SITE_URL . '/user/dashboard.php'),
    ];
}

function templateOrderPlacedEmail(array $order, string $serviceName, string $username): array
{
    $id = (int) $order['id'];
    $content = emailParagraph('Hi <strong>' . emailEscape($username) . '</strong>, your order has been confirmed and is now being processed.');
    $content .= emailDetailTable([
        'Order ID'  => '#' . $id,
        'Service'   => emailEscape($serviceName),
        'Quantity'  => number_format((int) $order['quantity']),
        'Link'      => '<a href="' . emailEscape($order['link']) . '" style="color:#4F46E5;word-break:break-all;">' . emailEscape($order['link']) . '</a>',
        'Charged'   => emailEscape(formatMoney((float) $order['charge'])),
        'Status'    => '<span style="color:#D97706;font-weight:600;">' . ucfirst(str_replace('_', ' ', $order['status'] ?? 'pending')) . '</span>',
    ]);
    $content .= emailParagraph('You can track progress anytime from your orders page.');

    return [
        'subject' => "Order #{$id} Confirmed ✅",
        'body'    => emailLayout("Order #{$id} Confirmed ✅", $content, 'View My Orders', SITE_URL . '/user/orders.php'),
    ];
}

function templateOrderCompletedEmail(array $order, string $serviceName, string $username): array
{
    $id = (int) $order['id'];
    $content = emailParagraph('Hi <strong>' . emailEscape($username) . '</strong>, great news — your order has been completed successfully! 🎉');
    $content .= emailDetailTable([
        'Order ID' => '#' . $id,
        'Service'  => emailEscape($serviceName),
        'Quantity' => number_format((int) $order['quantity']),
        'Link'     => '<a href="' . emailEscape($order['link']) . '" style="color:#4F46E5;word-break:break-all;">' . emailEscape($order['link']) . '</a>',
        'Status'   => '<span style="color:#059669;font-weight:600;">Completed</span>',
    ]);
    $content .= emailParagraph('Thank you for using ' . emailEscape(SITE_NAME) . '. Ready for your next boost? Browse our services and place another order.');

    return [
        'subject' => "Order #{$id} Completed! 🎉",
        'body'    => emailLayout("Order #{$id} Completed! 🎉", $content, 'Browse Services', SITE_URL . '/user/services.php'),
    ];
}

function templateOrderFailedEmail(array $order, string $serviceName, string $username, bool $refunded): array
{
    $id = (int) $order['id'];
    $content = emailParagraph('Hi <strong>' . emailEscape($username) . '</strong>, we\'re sorry — your order could not be completed.');
    $content .= emailDetailTable([
        'Order ID' => '#' . $id,
        'Service'  => emailEscape($serviceName),
        'Quantity' => number_format((int) $order['quantity']),
        'Link'     => emailEscape($order['link']),
        'Status'   => '<span style="color:#DC2626;font-weight:600;">Failed</span>',
    ]);
    if ($refunded) {
        $content .= emailParagraph('<strong style="color:#059669;">✓ Refund processed:</strong> ' . emailEscape(formatMoney((float) $order['charge'])) . ' has been credited back to your wallet balance.');
    } else {
        $content .= emailParagraph('Please contact our support team if you believe this was an error or need assistance.');
    }

    return [
        'subject' => "Order #{$id} Failed ❌",
        'body'    => emailLayout("Order #{$id} Failed ❌", $content, 'Contact Support', SITE_URL . '/user/tickets.php'),
    ];
}

function templateFundsAddedEmail(string $username, float $amount, float $newBalance): array
{
    $content = emailParagraph('Hi <strong>' . emailEscape($username) . '</strong>, your payment was successful and your wallet has been topped up!');
    $content .= emailDetailTable([
        'Amount Added' => emailEscape(formatMoney($amount)),
        'New Balance'  => '<span style="color:#059669;font-weight:700;">' . emailEscape(formatMoney($newBalance)) . '</span>',
        'Date'         => emailEscape(date('M j, Y g:i A T')),
    ]);
    $content .= emailParagraph('Your funds are ready to use. Browse our catalog and place your next order.');

    return [
        'subject' => 'Wallet Topped Up — ' . formatMoney($amount) . ' Added 💰',
        'body'    => emailLayout('Wallet Topped Up 💰', $content, 'Browse Services', SITE_URL . '/user/services.php'),
    ];
}

function templateTicketReplyEmail(string $username, string $subject, string $replyMessage, int $ticketId): array
{
    $preview = nl2br(emailEscape($replyMessage));
    $content = emailParagraph('Hi <strong>' . emailEscape($username) . '</strong>, our support team has replied to your ticket.');
    $content .= emailDetailTable([
        'Ticket'  => '#' . $ticketId . ' — ' . emailEscape($subject),
        'Subject' => emailEscape($subject),
    ]);
    $content .= '<div style="margin:16px 0;padding:16px 20px;background:#F3F4F6;border-left:4px solid #4F46E5;border-radius:0 8px 8px 0;font-size:14px;line-height:1.65;color:#374151;">' . $preview . '</div>';
    $content .= emailParagraph('You can reply directly from your ticket thread.');

    return [
        'subject' => 'Admin Replied to Your Ticket: ' . $subject,
        'body'    => emailLayout('New Reply on Your Ticket', $content, 'View Ticket', SITE_URL . '/user/ticket_view.php?id=' . $ticketId),
    ];
}

/* ─── Admin email templates ─── */

function templateAdminNewUserEmail(string $username, string $email): array
{
    $content = emailParagraph('A new user has registered on ' . emailEscape(SITE_NAME) . '.');
    $content .= emailDetailTable([
        'Username'     => emailEscape($username),
        'Email'        => emailEscape($email),
        'Registered'   => emailEscape(date('M j, Y g:i A T')),
    ]);

    return [
        'subject' => "New User Registered: {$username}",
        'body'    => emailLayout('New User Registration', $content, 'View Admin Panel', SITE_URL . '/admin/users.php'),
    ];
}

function templateAdminNewOrderEmail(array $order, string $serviceName, string $username, string $userEmail): array
{
    $id = (int) $order['id'];
    $content = emailParagraph('A new order has been placed on the panel.');
    $content .= emailDetailTable([
        'Order ID' => '#' . $id,
        'User'     => emailEscape($username) . ' (' . emailEscape($userEmail) . ')',
        'Service'  => emailEscape($serviceName),
        'Quantity' => number_format((int) $order['quantity']),
        'Amount'   => emailEscape(formatMoney((float) $order['charge'])),
        'Link'     => emailEscape($order['link']),
    ]);

    return [
        'subject' => "New Order #{$id} Received",
        'body'    => emailLayout("New Order #{$id}", $content, 'Manage Orders', SITE_URL . '/admin/orders.php'),
    ];
}

function templateAdminNewTicketEmail(int $ticketId, string $subject, string $message, string $username, string $userEmail): array
{
    $preview = emailEscape(mb_strlen($message) > 200 ? mb_substr($message, 0, 200) . '…' : $message);
    $content = emailParagraph('A new support ticket has been opened.');
    $content .= emailDetailTable([
        'Ticket ID' => '#' . $ticketId,
        'User'      => emailEscape($username) . ' (' . emailEscape($userEmail) . ')',
        'Subject'   => emailEscape($subject),
    ]);
    $content .= '<div style="margin:16px 0;padding:16px 20px;background:#FEF3C7;border-radius:8px;font-size:14px;line-height:1.65;color:#92400E;"><strong>Message preview:</strong><br>' . nl2br($preview) . '</div>';

    return [
        'subject' => "New Support Ticket: {$subject}",
        'body'    => emailLayout('New Support Ticket', $content, 'View Ticket', SITE_URL . '/admin/ticket_view.php?id=' . $ticketId),
    ];
}

function templateAdminPaymentEmail(string $username, string $userEmail, float $amount): array
{
    $content = emailParagraph('A new payment has been received.');
    $content .= emailDetailTable([
        'User'    => emailEscape($username) . ' (' . emailEscape($userEmail) . ')',
        'Amount'  => '<span style="color:#059669;font-weight:700;">' . emailEscape(formatMoney($amount)) . '</span>',
        'Date'    => emailEscape(date('M j, Y g:i A T')),
    ]);

    return [
        'subject' => 'Payment Received — ' . formatMoney($amount) . " from {$username}",
        'body'    => emailLayout('Payment Received 💰', $content, 'View Payments', SITE_URL . '/admin/payments.php'),
    ];
}
