<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="icon" href="assets/ascension.ico" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donate | Shaiya Ascension</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <?php $active_page = 'donate'; ?>
    <div class="main-wrapper">
        <?php include 'modules/header.php'; ?>
        <?php include 'modules/nav.php'; ?>

        <div class="content-area-box" style="padding: 40px 0;">
            <div class="donation-methods-container">
                <h1 class="donation-methods-title">Donation Methods</h1>

                <div class="methods-grid">
                    <div class="method-card active">
                        <h3>PayPal</h3>
                        <p><span class="highlight">Instant:</span> Points are added automatically right after payment.
                            Best for quick access.</p>
                    </div>
                    <div class="method-card">
                        <h3>Revolut</h3>
                        <p><span class="highlight">Manual:</span> Processed via Discord tickets. We will provide details
                            once a ticket is opened.</p>
                        <small style="color: #6b7280;">(Processing: up to 1h)</small>
                    </div>
                </div>

                <div class="manual-info-banner">
                    <strong>Manual Processing (Revolut):</strong> After sending your contribution, please open a support
                    ticket in our <a href="#">Discord Ticket System</a>. Include your <strong>Account ID</strong>, a
                    <strong>screenshot</strong> of the transfer, and the <strong>confirmation number</strong>.
                </div>

                <table class="packages-table">
                    <thead>
                        <tr>
                            <th colspan="2">ASCENSION POINTS</th>
                            <th>BONUS ITEM</th>
                            <th>AMOUNT (€)</th>
                            <th>BONUS (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="package-row">
                            <td class="package-selection"><input type="checkbox" class="custom-checkbox"></td>
                            <td class="points-val">200</td>
                            <td>
                                <div class="bonus-items-cell">
                                    <div class="item-icon" style="color: goldenrod;">
                                        <i class="fas fa-hammer"></i>
                                        <div class="item-tooltip">
                                            <div class="tooltip-icon"><i class="fas fa-hammer"></i></div>
                                            <span class="tooltip-title">Operator's Hammer</span>
                                            <span class="tooltip-desc">Increases the lapis link success rate
                                                significantly.</span>
                                            <span class="tooltip-qty">Quantity: 1</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="amount-val">5.00 €</td>
                            <td class="bonus-pct">0 %</td>
                        </tr>
                        <tr class="package-row">
                            <td class="package-selection"><input type="checkbox" class="custom-checkbox"></td>
                            <td class="points-val">500 + 25</td>
                            <td>
                                <div class="bonus-items-cell">
                                    <div class="item-icon" style="color: goldenrod;">
                                        <i class="fas fa-hammer"></i><span class="item-count">3</span>
                                        <div class="item-tooltip">
                                            <div class="tooltip-icon"><i class="fas fa-hammer"></i></div>
                                            <span class="tooltip-title">Operator's Hammer</span>
                                            <span class="tooltip-desc">Increases the lapis link success rate
                                                significantly.</span>
                                            <span class="tooltip-qty">Quantity: 3</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="amount-val">10.00 €</td>
                            <td class="bonus-pct">5 %</td>
                        </tr>
                        <tr class="package-row">
                            <td class="package-selection"><input type="checkbox" class="custom-checkbox"></td>
                            <td class="points-val">1,200 + 85 <span class="package-badge badge-popular">MOST
                                    POPULAR</span></td>
                            <td>
                                <div class="bonus-items-cell">
                                    <div class="item-icon" style="color: mediumpurple;">
                                        <i class="fas fa-hammer"></i>
                                        <div class="item-tooltip">
                                            <div class="tooltip-icon" style="color: mediumpurple;"><i
                                                    class="fas fa-hammer"></i></div>
                                            <span class="tooltip-title">Operators Exclusive</span>
                                            <span class="tooltip-desc">Multiplies the lapis % link rate x20 times the
                                                base.</span>
                                            <span class="tooltip-qty">Quantity: 1</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="amount-val">25.00 €</td>
                            <td class="bonus-pct">7 %</td>
                        </tr>
                        <tr class="package-row">
                            <td class="package-selection"><input type="checkbox" class="custom-checkbox"></td>
                            <td class="points-val">2,500 + 250</td>
                            <td>
                                <div class="bonus-items-cell">
                                    <div class="item-icon" style="color: mediumpurple;">
                                        <i class="fas fa-hammer"></i><span class="item-count">2</span>
                                        <div class="item-tooltip">
                                            <div class="tooltip-icon" style="color: mediumpurple;"><i
                                                    class="fas fa-hammer"></i></div>
                                            <span class="tooltip-title">Operators Exclusive</span>
                                            <span class="tooltip-desc">Multiplies the lapis % link rate x20 times the
                                                base.</span>
                                            <span class="tooltip-qty">Quantity: 2</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="amount-val">50.00 €</td>
                            <td class="bonus-pct">10 %</td>
                        </tr>
                        <tr class="package-row">
                            <td class="package-selection"><input type="checkbox" class="custom-checkbox"></td>
                            <td class="points-val">3,750 + 490</td>
                            <td>
                                <div class="bonus-items-cell">
                                    <div class="item-icon" style="color: mediumpurple;">
                                        <i class="fas fa-hammer"></i><span class="item-count">3</span>
                                        <div class="item-tooltip">
                                            <div class="tooltip-icon" style="color: mediumpurple;"><i
                                                    class="fas fa-hammer"></i></div>
                                            <span class="tooltip-title">Operators Exclusive</span>
                                            <span class="tooltip-desc">Multiplies the lapis % link rate x20 times the
                                                base.</span>
                                            <span class="tooltip-qty">Quantity: 3</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="amount-val">75.00 €</td>
                            <td class="bonus-pct">13 %</td>
                        </tr>
                        <tr class="package-row">
                            <td class="package-selection"><input type="checkbox" class="custom-checkbox"></td>
                            <td class="points-val">5,000 + 750 <span class="package-badge badge-value">BEST VALUE</span>
                            </td>
                            <td>
                                <div class="bonus-items-cell">
                                    <div class="item-icon" style="color: mediumpurple;">
                                        <i class="fas fa-hammer"></i><span class="item-count">5</span>
                                        <div class="item-tooltip">
                                            <div class="tooltip-icon" style="color: mediumpurple;"><i
                                                    class="fas fa-hammer"></i></div>
                                            <span class="tooltip-title">Operators Exclusive</span>
                                            <span class="tooltip-desc">Multiplies the lapis % link rate x20 times the
                                                base.</span>
                                            <span class="tooltip-qty">Quantity: 5</span>
                                        </div>
                                    </div>
                                    <div class="item-icon" style="color: tomato;">
                                        <i class="fas fa-box-open"></i><span class="item-count">10</span>
                                        <div class="item-tooltip">
                                            <div class="tooltip-icon" style="color: tomato;"><i
                                                    class="fas fa-box-open"></i></div>
                                            <span class="tooltip-title">Ascension Box</span>
                                            <span class="tooltip-desc">Contains rare items, recreation runes, and
                                                exclusive costumes.</span>
                                            <span class="tooltip-qty">Quantity: 10</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="amount-val">100.00 €</td>
                            <td class="bonus-pct">15 %</td>
                        </tr>
                        <tr class="package-row">
                            <td class="package-selection"><input type="checkbox" class="custom-checkbox"></td>
                            <td class="points-val">12,500 + 2,500</td>
                            <td>
                                <div class="bonus-items-cell">
                                    <div class="item-icon" style="color: lightgreen;">
                                        <i class="fas fa-hammer"></i>
                                        <div class="item-tooltip">
                                            <div class="tooltip-icon" style="color: lightgreen;"><i
                                                    class="fas fa-hammer"></i></div>
                                            <span class="tooltip-title">Ascension Hammer</span>
                                            <span class="tooltip-desc">The ultimate hammer. Guarantees maximum success
                                                for any lapis link.</span>
                                            <span class="tooltip-qty">Quantity: 1</span>
                                        </div>
                                    </div>
                                    <div class="item-icon" style="color: tomato;">
                                        <i class="fas fa-box-open"></i><span class="item-count">30</span>
                                        <div class="item-tooltip">
                                            <div class="tooltip-icon" style="color: tomato;"><i
                                                    class="fas fa-box-open"></i></div>
                                            <span class="tooltip-title">Ascension Box</span>
                                            <span class="tooltip-desc">Contains rare items, recreation runes, and
                                                exclusive costumes.</span>
                                            <span class="tooltip-qty">Quantity: 30</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="amount-val">250.00 €</td>
                            <td class="bonus-pct">20 %</td>
                        </tr>
                    </tbody>
                </table>

                <button class="btn-donate-paypal">
                    <i class="fab fa-paypal"></i> Donate!
                </button>
            </div>
        </div>
    </div>
    <?php include 'modules/footer.php'; ?>

    <script>
        document.querySelectorAll('.custom-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                if (this.checked) {
                    document.querySelectorAll('.custom-checkbox').forEach(other => {
                        if (other !== this) other.checked = false;
                    });
                }
            });
        });
    </script>
</body>

</html>