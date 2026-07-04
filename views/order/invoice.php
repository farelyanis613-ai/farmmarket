<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$orderId       = intval($order['id'] ?? 0);
$issueDate     = date('d/m/Y', strtotime($order['created_at'] ?? 'now'));
$orderDate     = date('d/m/Y a H:i', strtotime($order['created_at'] ?? 'now'));
$statusLabel   = htmlspecialchars(formatStatusLabel($order['status'] ?? 'pending'));
$deliveryType  = isset($order['delivery_type']) && $order['delivery_type'] === 'home' ? 'A domicile' : 'Retrait en magasin';
$addressText   = htmlspecialchars($order['address'] ?? 'N/A');
$customerName  = htmlspecialchars($order['customer_name']  ?? $_SESSION['user']['name']  ?? '');
$customerEmail = htmlspecialchars($order['customer_email'] ?? $_SESSION['user']['email'] ?? '');
$customerPhone = htmlspecialchars($order['phone'] ?? $order['customer_phone'] ?? $_SESSION['user']['phone'] ?? '');

$subtotal = 0;
$itemRows = '';
$rowIndex = 0;
foreach ($orderItems as $item) {
    $rowIndex++;
    $lineTotal  = (float)($item['unit_price'] ?? 0) * (int)($item['quantity'] ?? 0);
    $subtotal  += $lineTotal;
    $bg         = $rowIndex % 2 === 0 ? '#F0F4FF' : '#FFFFFF';
    $itemRows  .= sprintf(
        '<tr style="background:%s;">
            <td style="padding:11px 14px; border-bottom:1px solid #E8EEF8; color:#0F172A; font-size:11.5px; font-weight:500;">%s</td>
            <td style="padding:11px 14px; text-align:center; border-bottom:1px solid #E8EEF8; color:#64748B; font-size:11px;">%d</td>
            <td style="padding:11px 14px; text-align:right; border-bottom:1px solid #E8EEF8; color:#475569; font-size:11px;">%s FCFA</td>
            <td style="padding:11px 14px; text-align:right; border-bottom:1px solid #E8EEF8; font-weight:700; color:#1D4ED8; font-size:11.5px;">%s FCFA</td>
        </tr>',
        $bg,
        htmlspecialchars($item['product_name'] ?? 'Produit'),
        (int)($item['quantity'] ?? 0),
        number_format((float)($item['unit_price'] ?? 0), 0, '', ' '),
        number_format($lineTotal, 0, '', ' ')
    );
}

$deliveryFee       = (float)($order['delivery_fee'] ?? 0);
$totalPrice        = (float)($order['total_price'] ?? 0);
$subtotalFormatted = number_format($subtotal,     0, '', ' ');
$deliveryFormatted = number_format($deliveryFee,  0, '', ' ');
$totalFormatted    = number_format($totalPrice,   0, '', ' ');
$deliverySign      = $deliveryFee > 0 ? '+' : '';
$invoiceNumber     = 'FAC-' . date('Y') . '-' . str_pad($orderId, 5, '0', STR_PAD_LEFT);
$year              = date('Y');

$html = <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: DejaVu Sans, Arial, sans-serif;
    color: #0F172A;
    background: #F1F5F9;
    font-size: 11px;
    line-height: 1.55;
  }

  .page {
    width: 210mm;
    padding: 18px 20px 24px;
    background: #F1F5F9;
  }

  /* ══════════════════════════════════
     HEADER PREMIUM
  ══════════════════════════════════ */
  .header {
    background: #1E3A8A;
    padding: 0;
    margin-bottom: 14px;
  }
  .header-top {
    background: #1D4ED8;
    padding: 20px 26px 18px;
    display: table;
    width: 100%;
  }
  .header-bottom {
    background: #1E3A8A;
    padding: 8px 26px;
    display: table;
    width: 100%;
  }
  .hl { display: table-cell; vertical-align: middle; width: 58%; }
  .hr { display: table-cell; vertical-align: middle; text-align: right; width: 42%; }

  .brand-name {
    font-size: 24px;
    font-weight: 700;
    color: #FFFFFF;
    letter-spacing: -0.3px;
  }
  .brand-dot { color: #60A5FA; }
  .brand-tagline {
    font-size: 9.5px;
    color: rgba(255,255,255,0.6);
    margin-top: 3px;
    letter-spacing: 0.04em;
  }

  .inv-badge {
    font-size: 8.5px;
    font-weight: 700;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: #93C5FD;
  }
  .inv-num {
    font-size: 20px;
    font-weight: 700;
    color: #FFFFFF;
    margin-top: 2px;
    letter-spacing: 0.5px;
  }
  .inv-date {
    font-size: 9px;
    color: rgba(255,255,255,0.55);
    margin-top: 3px;
  }

  .status-pill {
    display: inline-block;
    padding: 3px 12px;
    font-size: 8.5px;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #BFDBFE;
    border: 1px solid rgba(191,219,254,0.35);
  }

  .hb-left  { display: table-cell; vertical-align: middle; width: 60%; }
  .hb-right { display: table-cell; vertical-align: middle; text-align: right; width: 40%; }
  .hb-contact {
    font-size: 9px;
    color: rgba(255,255,255,0.45);
    line-height: 1.9;
  }
  .hb-ref {
    font-size: 9px;
    color: rgba(255,255,255,0.45);
  }
  .hb-ref strong { color: #93C5FD; }

  /* ══════════════════════════════════
     CARTES INFO
  ══════════════════════════════════ */
  .info-grid { display: table; width: 100%; margin-bottom: 14px; }

  .info-card {
    display: table-cell;
    width: 50%;
    background: #FFFFFF;
    border-top: 3px solid #1D4ED8;
    padding: 14px 16px;
    vertical-align: top;
  }
  .info-card.left  { margin-right: 7px; }
  .info-card.right { border-left: none; border-top-color: #0EA5E9; }

  .card-label {
    font-size: 7.5px;
    font-weight: 700;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: #94A3B8;
    margin-bottom: 10px;
    padding-bottom: 6px;
    border-bottom: 1px solid #F1F5F9;
  }
  .info-name  { font-size: 12px; font-weight: 700; color: #0F172A; margin-bottom: 5px; }
  .info-sub   { font-size: 10px; color: #475569; margin-bottom: 3px; line-height: 1.65; }
  .info-sub strong { color: #1E3A8A; font-weight: 700; }

  /* ══════════════════════════════════
     SECTION TITRE
  ══════════════════════════════════ */
  .sec-head {
    display: table;
    width: 100%;
    margin-bottom: 8px;
  }
  .sec-head-left  { display: table-cell; vertical-align: middle; }
  .sec-title {
    font-size: 8px;
    font-weight: 700;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: #64748B;
  }
  .sec-line {
    display: table-cell;
    vertical-align: middle;
    width: 100%;
    padding-left: 10px;
  }
  .sec-line-inner {
    height: 1px;
    background: #E2E8F0;
  }

  /* ══════════════════════════════════
     TABLEAU ARTICLES
  ══════════════════════════════════ */
  .items-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 14px;
    border: 1px solid #E2E8F0;
    background: #FFFFFF;
  }
  .items-table thead tr { background: #1D4ED8; }
  .items-table thead th {
    padding: 10px 14px;
    font-size: 8.5px;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: #FFFFFF;
  }
  .items-table tbody tr:last-child td { border-bottom: none; }

  /* ══════════════════════════════════
     TOTAUX
  ══════════════════════════════════ */
  .totals-wrap { display: table; width: 100%; margin-bottom: 14px; }
  .totals-gap  { display: table-cell; width: 48%; }
  .totals-box  {
    display: table-cell;
    width: 52%;
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-top: 3px solid #0EA5E9;
    padding: 14px 16px;
    vertical-align: top;
  }

  .t-row {
    display: table;
    width: 100%;
    padding: 7px 0;
    border-bottom: 1px solid #F8FAFC;
  }
  .t-lbl { display: table-cell; font-size: 10px; color: #64748B; }
  .t-val { display: table-cell; text-align: right; font-size: 10px; font-weight: 600; color: #334155; }

  /* LIGNE TOTAL VERTE PREMIUM */
  .total-final {
    display: table;
    width: 100%;
    margin-top: 10px;
    background: #ECFDF5;
    border: 2px solid #10B981;
    padding: 12px 14px;
  }
  .total-final-lbl {
    display: table-cell;
    font-size: 11px;
    font-weight: 700;
    color: #064E3B;
    vertical-align: middle;
  }
  .total-final-val {
    display: table-cell;
    text-align: right;
    font-size: 15px;
    font-weight: 700;
    color: #059669;
    vertical-align: middle;
    letter-spacing: -0.3px;
  }
  .total-currency {
    font-size: 10px;
    font-weight: 700;
    color: #10B981;
    margin-left: 3px;
  }

  /* ══════════════════════════════════
     FOOTER
  ══════════════════════════════════ */
  .footer-wrap {
    display: table;
    width: 100%;
    background: #FFFFFF;
    border-top: 3px solid #1D4ED8;
    padding: 14px 18px;
  }
  .footer-left  { display: table-cell; vertical-align: middle; width: 55%; }
  .footer-right { display: table-cell; vertical-align: middle; text-align: right; width: 45%; }

  .footer-thanks {
    font-size: 11.5px;
    font-weight: 700;
    color: #1E3A8A;
    margin-bottom: 3px;
  }
  .footer-sub {
    font-size: 9px;
    color: #94A3B8;
    line-height: 1.7;
  }
  .footer-legal {
    font-size: 8px;
    color: #CBD5E1;
    margin-top: 4px;
  }
  .footer-badge {
    display: inline-block;
    background: #EFF6FF;
    border: 1px solid #BFDBFE;
    padding: 5px 12px;
    font-size: 9px;
    font-weight: 700;
    color: #1D4ED8;
    letter-spacing: 0.05em;
  }
</style>
</head>
<body>
<div class="page">

  <!-- ══ HEADER ══ -->
  <div class="header">
    <div class="header-top">
      <div class="hl">
        <div class="brand-name">Farm<span class="brand-dot">.</span>Market</div>
        <div class="brand-tagline">Produits frais · Livraison rapide · Qualite garantie</div>
      </div>
      <div class="hr">
        <div class="inv-badge">Facture commerciale</div>
        <div class="inv-num">{$invoiceNumber}</div>
        <div class="inv-date">Emise le {$issueDate}</div>
        <div style="margin-top:6px;"><span class="status-pill">{$statusLabel}</span></div>
      </div>
    </div>
    <div class="header-bottom">
      <div class="hb-left">
        <div class="hb-contact">
          123 Rue de la Ferme, Cotonou, Benin &nbsp;·&nbsp;
          contact@farmmarket.com &nbsp;·&nbsp; +229 97 00 00 00
        </div>
      </div>
      <div class="hb-right">
        <div class="hb-ref">Ref. commande : <strong>#CMD-{$orderId}</strong></div>
      </div>
    </div>
  </div>

  <!-- ══ INFOS CLIENT + COMMANDE ══ -->
  <div class="info-grid">
    <div class="info-card left">
      <div class="card-label">Facture a</div>
      <div class="info-name">{$customerName}</div>
      <div class="info-sub">{$customerEmail}</div>
      <div class="info-sub">{$customerPhone}</div>
      <div class="info-sub" style="margin-top:6px; color:#334155;">{$addressText}</div>
    </div>
    <div class="info-card right">
      <div class="card-label">Details commande</div>
      <div class="info-sub"><strong>Date</strong> &nbsp; {$orderDate}</div>
      <div class="info-sub"><strong>Mode</strong> &nbsp; {$deliveryType}</div>
      <div class="info-sub"><strong>Frais livraison</strong> &nbsp; {$deliveryFormatted} FCFA</div>
      <div class="info-sub" style="margin-top:6px;"><strong>Emetteur</strong> &nbsp; FarmMarket SARL</div>
    </div>
  </div>

  <!-- ══ TABLEAU ARTICLES ══ -->
  <div class="sec-head">
    <div class="sec-head-left"><div class="sec-title">Articles commandes</div></div>
    <div class="sec-line"><div class="sec-line-inner"></div></div>
  </div>

  <table class="items-table">
    <thead>
      <tr>
        <th style="text-align:left;">Designation</th>
        <th style="text-align:center; width:65px;">Qte</th>
        <th style="text-align:right; width:115px;">Prix unitaire</th>
        <th style="text-align:right; width:115px;">Montant</th>
      </tr>
    </thead>
    <tbody>{$itemRows}</tbody>
  </table>

  <!-- ══ TOTAUX ══ -->
  <div class="totals-wrap">
    <div class="totals-gap"></div>
    <div class="totals-box">
      <div class="t-row">
        <span class="t-lbl">Sous-total HT</span>
        <span class="t-val">{$subtotalFormatted} FCFA</span>
      </div>
      <div class="t-row">
        <span class="t-lbl">Frais de livraison</span>
        <span class="t-val">{$deliverySign}{$deliveryFormatted} FCFA</span>
      </div>
      <div class="t-row" style="border-bottom:none;">
        <span class="t-lbl" style="font-size:9px; color:#94A3B8;">TVA / Taxes</span>
        <span class="t-val" style="color:#94A3B8;">Inclus</span>
      </div>
      <div class="total-final">
        <span class="total-final-lbl">Total paye</span>
        <span class="total-final-val">{$totalFormatted}<span class="total-currency">FCFA</span></span>
      </div>
    </div>
  </div>

  <!-- ══ FOOTER ══ -->
  <div class="footer-wrap">
    <div class="footer-left">
      <div class="footer-thanks">Merci pour votre commande !</div>
      <div class="footer-sub">
        Pour toute question : contact@farmmarket.com · +229 97 00 00 00<br>
        FarmMarket SARL · 123 Rue de la Ferme, Cotonou, Benin
      </div>
      <div class="footer-legal">
        Document genere automatiquement · valable sans signature · {$year} FarmMarket
      </div>
    </div>
    <div class="footer-right">
      <div class="footer-badge">Paiement confirme</div>
    </div>
  </div>

</div>
</body>
</html>
HTML;

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', false);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream('facture-' . $invoiceNumber . '.pdf', ['Attachment' => true]);