<?php
require_once 'db.php';

// Fetch Downloads Settings
$dlEnabled = '1';
$linkGoogle = '';
$linkMirror = '';
$linkMega = '';

$qDl = odbc_exec($conn, "IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[PS_UserData].[dbo].[Web_Settings]') AND type in (N'U')) SELECT SettingKey, SettingValue FROM PS_UserData.dbo.Web_Settings WHERE SettingKey IN ('DownloadsEnabled', 'Link_GoogleDrive', 'Link_OfficialMirror', 'Link_Mega')");
while ($qDl && ($row = odbc_fetch_array($qDl))) {
    if ($row['SettingKey'] == 'DownloadsEnabled')
        $dlEnabled = $row['SettingValue'];
    if ($row['SettingKey'] == 'Link_GoogleDrive')
        $linkGoogle = $row['SettingValue'];
    if ($row['SettingKey'] == 'Link_OfficialMirror')
        $linkMirror = $row['SettingValue'];
    if ($row['SettingKey'] == 'Link_Mega')
        $linkMega = $row['SettingValue'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="icon" href="assets/ascension.ico" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download | Shaiya Ascension</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <?php $active_page = 'download'; ?>
    <div class="main-wrapper">
        <?php include 'modules/header.php'; ?>
        <?php include 'modules/nav.php'; ?>

        <div class="content-area-box">
            <div class="content-header">
                <h2><i class="fas fa-download"></i> Game Download</h2>
            </div>
            <div class="content-body" style="text-align: center; padding: 40px;">
                <?php if ($dlEnabled == '1'): ?>
                    <p style="color: #d1d5db; margin-bottom: 25px;">Download the official Shaiya Ascension client below.
                        Ensure your system meets the requirements.</p>

                    <div class="download-links" style="max-width: 650px; margin: 0 auto 30px;">
                        <div class="dl-btn">
                            <i class="fab fa-google-drive dl-icon"></i>
                            <div class="dl-text" style="text-align: left; flex-grow: 1;">
                                <h3>Google Drive</h3>
                                <p>Full Client v4.5.3 (2.4 GB) - Fastest</p>
                            </div>
                            <a href="<?php echo htmlspecialchars($linkGoogle); ?>" target="_blank"
                                class="dl-action">Download</a>
                        </div>
                        <div class="dl-btn">
                            <i class="fas fa-server dl-icon"></i>
                            <div class="dl-text" style="text-align: left; flex-grow: 1;">
                                <h3>Official Mirror</h3>
                                <p>Full Client v4.5.3 (2.4 GB) - Direct</p>
                            </div>
                            <a href="<?php echo htmlspecialchars($linkMirror); ?>" target="_blank"
                                class="dl-action">Download</a>
                        </div>
                        <div class="dl-btn">
                            <i class="fas fa-file-archive dl-icon"></i>
                            <div class="dl-text" style="text-align: left; flex-grow: 1;">
                                <h3>Mega.nz</h3>
                                <p>Full Client v4.5.3 (2.4 GB) - Mirror 2</p>
                            </div>
                            <a href="<?php echo htmlspecialchars($linkMega); ?>" target="_blank"
                                class="dl-action">Download</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div
                        style="max-width: 600px; margin: 0 auto 30px; text-align: center; padding: 40px; background: rgba(0,0,0,0.2); border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                        <i class="fas fa-clock"
                            style="font-size: 48px; color: var(--text-gold); opacity: 0.5; margin-bottom: 20px;"></i>
                        <h3 style="font-family: 'Cinzel', serif; color: #fff; margin-bottom: 15px;">Downloads will be
                            available soon!</h3>
                        <p style="color: #a3a3a3; line-height: 1.6;">We are currently working on updating our client files
                            and mirrors. Game downloads are temporarily unavailable but will be back online very shortly. We
                            appreciate your patience!</p>
                    </div>
                <?php endif; ?>

                <div class="info-block" style="max-width: 650px; margin: 0 auto; text-align: left;">
                    <h3><i class="fas fa-desktop"></i> System Requirements</h3>
                    <ul class="info-list" style="list-style: none; padding: 0;">
                        <li><span style="color: #9ca3af;">OS</span> <span class="val">Windows 10 / 11 (64-bit)</span>
                        </li>
                        <li><span style="color: #9ca3af;">CPU</span> <span class="val">Core i3 or higher</span></li>
                        <li><span style="color: #9ca3af;">RAM</span> <span class="val">4 GB or more</span></li>
                        <li><span style="color: #9ca3af;">Graphics</span> <span class="val">Geforce GTX 650+ / Radeon
                                R7+</span></li>
                        <li><span style="color: #9ca3af;">Storage</span> <span class="val">10 GB Free Space (SSD
                                Recommended)</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?php include 'modules/footer.php'; ?>
</body>

</html>