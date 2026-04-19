<?php
session_start();
require_once 'db.php';


// Logic moved to modules/stats_logic.php and included in modules/header.php


$login_error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Using UserID and Pw from Users_Master as requested
    $query = "SELECT UserID, AdminLevel, Point FROM Users_Master WHERE UserID = ? AND Pw = ?";
    $stmt = odbc_prepare($conn, $query);
    odbc_execute($stmt, [$username, $password]);

    if (odbc_fetch_row($stmt)) {
        $_SESSION['user'] = odbc_result($stmt, "UserID");
        $_SESSION['admin_level'] = odbc_result($stmt, "AdminLevel");
        $_SESSION['points'] = (int) odbc_result($stmt, "Point");
    } else {
        $login_error = "Invalid username or password.";
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Fetch live Nostalgia Points & Status from database
$nostalgia_points = 0;
$userStatus = 0;
if (isset($_SESSION['user'])) {
    $pts_query = "SELECT Point, Status FROM PS_UserData.dbo.Users_Master WHERE UserID = ?";
    $pts_stmt = odbc_prepare($conn, $pts_query);
    if ($pts_stmt && odbc_execute($pts_stmt, [$_SESSION['user']])) {
        $pts_row = odbc_fetch_array($pts_stmt);
        if ($pts_row) {
            $nostalgia_points = (int) $pts_row['Point'];
            $userStatus = (int) $pts_row['Status'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="icon" href="assets/ascension.ico" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News | Shaiya Ascension</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        /* Quill Editor Dark Mode Overrides */
        .ql-toolbar.ql-snow {
            background: #222 !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
            border-top-left-radius: 4px;
            border-top-right-radius: 4px;
        }

        .ql-snow .ql-stroke {
            stroke: #ddd !important;
        }

        .ql-snow .ql-fill {
            fill: #ddd !important;
        }

        .ql-snow .ql-picker {
            color: #ddd !important;
        }

        .ql-snow .ql-picker-options {
            background-color: #222 !important;
            border-color: #444 !important;
        }

        .ql-snow .ql-active .ql-stroke,
        .ql-snow .ql-picker-label.ql-active .ql-stroke,
        .ql-snow .ql-active .ql-fill,
        .ql-container.ql-snow .ql-active {
            stroke: #e8c881 !important;
            fill: #e8c881 !important;
            color: #e8c881 !important;
        }

        /* QUILL THEME OVERRIDES */
        .ql-toolbar.ql-snow {
            background: #252528 !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
            border-top-left-radius: 4px;
            border-top-right-radius: 4px;
        }

        .ql-container.ql-snow {
            border-color: rgba(255, 255, 255, 0.1) !important;
            background: #111 !important;
            color: #eee !important;
            border-bottom-left-radius: 4px;
            border-bottom-right-radius: 4px;
            font-family: var(--primary-font) !important;
            font-size: 14px !important;
        }

        .ql-editor.ql-blank::before {
            color: #666 !important;
            font-style: italic !important;
        }

        /* FIXING ARTICLE BUTTONS POSITION */
        .news-article {
            overflow: visible !important;
            display: block !important;
            padding-bottom: 20px !important;
            /* Force space below buttons */
        }

        .news-admin-actions {
            margin: 15px 20px 20px 20px !important;
            /* Ensure buttons have space within the padding-box */
            padding-top: 15px;
            border-top: 1px dashed rgba(255, 255, 255, 0.1);
            display: flex;
            gap: 10px;
        }

        .news-body img {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
            margin: 15px 0;
            display: block;
        }

        /* Quill Tooltip (Link/Video popups) */
        .ql-snow .ql-tooltip {
            background-color: #222 !important;
            color: #ddd !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5) !important;
            border-radius: 4px !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
        }

        .ql-snow .ql-tooltip input[type=text] {
            background: #111 !important;
            color: #fff !important;
            border: 1px solid #444 !important;
            border-radius: 3px !important;
            padding: 5px !important;
        }

        .ql-snow .ql-tooltip a.ql-action::after {
            color: #e8c881 !important;
        }

        .ql-snow .ql-tooltip a.ql-preview {
            color: #aaa !important;
        }
    </style>
</head>

<body>
    <?php $active_page = 'news'; ?>
    <div class="main-wrapper">
        <?php include 'modules/header.php'; ?>
        <?php include 'modules/nav.php'; ?>

        <!-- Content Area -->
        <div class="content-container">
            <!-- Main Content (News) -->
            <main class="content-main">
                <?php if ($userStatus == 16): ?>
                    <div class="news-article"
                        style="border: 1px solid var(--text-gold); background: rgba(255,215,0,0.05); padding: 20px 20px 40px 20px; border-radius: 8px; margin-bottom: 30px;">
                        <h3 style="color: var(--text-gold); margin-top: 0; margin-bottom: 15px;"><i class="fas fa-pen"></i>
                            Create News Post</h3>
                        <form action="news_handler.php" method="POST" id="news-form">
                            <input type="hidden" name="action" value="create">
                            <input type="text" name="title" placeholder="News Title (Max 45 chars)" required maxlength="45"
                                style="width: 100%; padding: 10px; margin-bottom: 10px; background: rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.1); color: #fff; border-radius: 4px; font-family: inherit;">

                            <div id="editor-container"
                                style="height: 200px; background: #fff; color: #000; border-radius: 4px; margin-bottom: 15px;">
                            </div>
                            <input type="hidden" name="content" id="hidden-content">

                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <button type="submit" class="btn-primary"
                                    style="background: linear-gradient(135deg, #e8c881, #b1935e); border: none; padding: 10px 20px; color: #111; font-weight: bold; border-radius: 4px; cursor: pointer; text-transform: uppercase; font-size: 13px;"
                                    onclick="document.getElementById('hidden-content').value = quill.root.innerHTML;"><i
                                        class="fas fa-paper-plane"></i> Deploy News</button>

                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <input type="checkbox" name="is_hidden" id="create_is_hidden"
                                        style="width: 18px; height: 18px; cursor: pointer; accent-color: var(--text-gold);">
                                    <label for="create_is_hidden"
                                        style="color: rgba(255,255,255,0.7); font-size: 13px; cursor: pointer; font-family: var(--primary-font); font-weight: 500;">Post
                                        as Hidden</label>
                                </div>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>

                <?php
                // Fetch dynamic news from Web_News Table
                $newsCheck = @odbc_exec($conn, "SELECT NewsID, Title, Content, Author, CreatedAt, IsHidden FROM PS_UserData.dbo.Web_News ORDER BY CreatedAt DESC");
                $hasNews = false;

                // If table doesn't exist yet or forms return no results, fall back to simple empty state
                if ($newsCheck && odbc_num_rows($newsCheck) > 0):
                    while ($news = odbc_fetch_array($newsCheck)):
                        $n_id = (int) $news['NewsID'];
                        $n_title = htmlspecialchars($news['Title']);
                        $n_content = $news['Content']; // HTML Content from WYSIWYG
                        $n_date = date('F j, Y', strtotime($news['CreatedAt']));
                        $n_hidden = (int) $news['IsHidden'];

                        // Hide from normal users if IsHidden == 1
                        if ($n_hidden && $userStatus != 16)
                            continue;

                        $hasNews = true;
                        ?>
                        <div class="news-article" data-id="<?php echo $n_id; ?>" <?php if ($n_hidden)
                               echo 'style="border-color: #f46a6a; box-shadow: 0 0 15px rgba(244, 106, 106, 0.15);"'; ?>>
                            <div class="news-header">
                                <h2><?php echo $n_title; ?>
                                    <?php if ($n_hidden)
                                        echo '<span style="color: #f46a6a; font-size: 11px; margin-left: 10px; background: rgba(244, 106, 106, 0.1); padding: 4px 10px; border-radius: 4px; border: 1px solid rgba(244, 106, 106, 0.2); vertical-align: middle; letter-spacing: 0.5px;">HIDDEN DRAFT</span>'; ?>
                                </h2>
                                <span class="news-date"><?php echo $n_date; ?></span>
                            </div>
                            <div class="news-body" style="word-break: break-word;">
                                <?php echo $n_content; ?>
                            </div>
                            <?php if ($userStatus == 16): ?>
                                <div class="news-admin-actions"
                                    style="margin-top: 15px; padding-top: 15px; border-top: 1px dashed rgba(255,255,255,0.1); display: flex; gap: 10px;">
                                    <form action="news_handler.php" method="POST" style="margin:0;">
                                        <input type="hidden" name="action" value="toggle_visibility">
                                        <input type="hidden" name="news_id" value="<?php echo $n_id; ?>">
                                        <button type="submit"
                                            style="background: #252525; color: #fff; border: 1px solid #444; padding: 6px 12px; cursor: pointer; border-radius: 3px; font-size: 11px; font-weight: bold;"><i
                                                class="fas fa-eye<?php echo $n_hidden ? '' : '-slash'; ?>"></i>
                                            <?php echo $n_hidden ? 'SHOW' : 'HIDE'; ?></button>
                                    </form>

                                    <button type="button" onclick="editNews(<?php echo $n_id; ?>)"
                                        style="background: #252525; color: #fff; border: 1px solid #444; padding: 6px 12px; cursor: pointer; border-radius: 3px; font-size: 11px; font-weight: bold;"><i
                                            class="fas fa-pen"></i> EDIT</button>

                                    <form action="news_handler.php" method="POST" style="margin:0;"
                                        onsubmit="return confirm('Are you sure you want to delete this news post completely?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="news_id" value="<?php echo $n_id; ?>">
                                        <button type="submit"
                                            style="background: #3a1111; color: #fff; border: 1px solid #8b0000; padding: 6px 12px; cursor: pointer; border-radius: 3px; font-size: 11px; font-weight: bold;"><i
                                                class="fas fa-trash"></i> DELETE</button>
                                    </form>
                                </div>

                                <div id="raw-content-<?php echo $n_id; ?>" style="display: none;">
                                    <?php echo htmlspecialchars($n_content); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile;
                endif;

                if (!$hasNews): ?>
                    <div class="news-article" style="text-align: center; padding: 40px;">
                        <p style="color: #777; font-style: italic;">No active news to display.</p>
                    </div>
                <?php endif; ?>
            </main>

            <!-- Right Sidebar -->
            <aside class="sidebar sidebar-right">
                <!-- User Panel -->
                <div class="side-box">
                    <div class="box-header"><i class="fas fa-user"></i> User Control Panel</div>
                    <div class="box-content login-box">
                        <?php if (isset($_SESSION['user'])): ?>

                            <div style="text-align: center; margin-bottom: 15px;">
                                <div
                                    style="font-family: 'Cinzel', serif; font-size: 14px; text-transform: uppercase; letter-spacing: 1.5px; color: #d1d5db; margin-bottom: 2px;">
                                    Welcome Back</div>
                                <div
                                    style="font-family: 'Cinzel', serif; font-size: 16px; color: var(--text-gold); font-weight: 700; letter-spacing: 1px;">
                                    <?php echo htmlspecialchars($_SESSION['user']); ?>
                                </div>
                                <div
                                    style="margin-top: 12px; padding-top: 12px; border-top: 1px solid rgba(255,255,255,0.05);">
                                    <div
                                        style="font-family: 'Cinzel', serif; font-size: 13px; text-transform: uppercase; letter-spacing: 1px; color: #d1d5db;">
                                        Ascension Points</div>
                                    <div
                                        style="font-family: 'Cinzel', serif; font-size: 20px; color: var(--text-gold); font-weight: 700; margin-top: 2px;">
                                        <?php echo number_format($nostalgia_points); ?>
                                    </div>
                                </div>
                            </div>

                            <div style="padding-top: 12px; border-top: 1px solid rgba(255,255,255,0.05);"></div>

                            <div style="display: flex; justify-content: center;">
                                <a href="account.php" style="
                                    background: rgba(232, 200, 129, 0.1);
                                    color: #e8c881;
                                    border: 1px solid #e8c881;
                                    text-decoration: none;
                                    padding: 8px 16px;
                                    border-radius: 4px;
                                    font-family: 'Cinzel', serif;
                                    font-weight: 700;
                                    font-size: 13px;
                                    display: inline-flex;
                                    align-items: center;
                                    gap: 8px;
                                    transition: all 0.3s ease;
                                    text-transform: uppercase;
                                    letter-spacing: 1px;
                                "
                                    onmouseover="this.style.background='#e8c881'; this.style.color='#000'; this.style.boxShadow='0 0 15px rgba(232, 200, 129, 0.4)';"
                                    onmouseout="this.style.background='rgba(232, 200, 129, 0.1)'; this.style.color='#e8c881'; this.style.boxShadow='none';">
                                    <i class="fas fa-user-circle" style="font-size: 15px;"></i> My Account
                                </a>
                            </div>
                        <?php else: ?>
                            <?php if (!empty($login_error)): ?>
                                <div style="color: #ff4d4d; font-size: 12px; margin-bottom: 10px; text-align: center;">
                                    <?php echo $login_error; ?>
                                </div>
                            <?php endif; ?>
                            <form action="index.php" method="POST">
                                <input type="text" name="username" placeholder="Username" required>
                                <input type="password" name="password" placeholder="Password" required>
                                <button type="submit" name="login" class="btn-login">Login</button>
                            </form>
                            <div
                                style="display: flex; justify-content: center; margin-top: 15px; padding-top: 12px; border-top: 1px solid rgba(255,255,255,0.05);">
                                <a href="register.php" style="
                                    background: linear-gradient(135deg, rgba(232, 200, 129, 0.2), rgba(232, 200, 129, 0.05));
                                    color: #e8c881;
                                    border: 1px solid #e8c881;
                                    text-decoration: none;
                                    padding: 10px 20px;
                                    border-radius: 4px;
                                    font-family: 'Cinzel', serif;
                                    font-weight: 700;
                                    font-size: 13px;
                                    display: inline-flex;
                                    align-items: center;
                                    gap: 8px;
                                    transition: all 0.3s ease;
                                    text-transform: uppercase;
                                    letter-spacing: 1.5px;
                                    box-shadow: 0 0 10px rgba(232, 200, 129, 0.1);
                                "
                                    onmouseover="this.style.background='#e8c881'; this.style.color='#000'; this.style.boxShadow='0 0 20px rgba(232, 200, 129, 0.5)';"
                                    onmouseout="this.style.background='linear-gradient(135deg, rgba(232, 200, 129, 0.2), rgba(232, 200, 129, 0.05))'; this.style.color='#e8c881'; this.style.boxShadow='0 0 10px rgba(232, 200, 129, 0.1)';">
                                    <i class="fas fa-user-plus" style="font-size: 15px;"></i> Create Account
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Server Info -->
                <div class="side-box">
                    <div class="box-header"><i class="fas fa-info-circle"></i> Server Info</div>
                    <div class="box-content server-info-box">
                        <table class="server-info-table">
                            <tr>
                                <td class="info-label">Server Time</td>
                                <td class="info-value" id="server-time"><?php echo date('H:i:s'); ?></td>
                            </tr>
                            <tr>
                                <td class="info-label">GRB Timer</td>
                                <td class="info-value" id="grb-timer">Calculating...</td>
                            </tr>
                            <tr>
                                <td class="info-label">Episode</td>
                                <td class="info-value">4.5</td>
                            </tr>
                            <tr>
                                <td class="info-label">Max. Level</td>
                                <td class="info-value">60</td>
                            </tr>
                            <tr>
                                <td class="info-label">EXP Rate</td>
                                <td class="info-value">x100 (Weekdays)<br>x250 (Weekends)</td>
                            </tr>
                            <tr>
                                <td class="info-label">Kill Rate</td>
                                <td class="info-value">x1</td>
                            </tr>
                            <tr>
                                <td class="info-label">Max. Enchantment</td>
                                <td class="info-value">[10]</td>
                            </tr>
                            <tr>
                                <td class="info-label">Max. Lapis</td>
                                <td class="info-value">Level 5<br>Element Level 1<br>Sonic/Flash Level 1</td>
                            </tr>
                            <tr>
                                <td class="info-label">Free</td>
                                <td class="info-value">Fortune Box<br>30 Day Items<br>Free Starter Gears</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Sidebar Statistics moved to Header -->

            </aside>
        </div>
    </div>



    <script src="js/script.js?v=<?php echo time(); ?>"></script>
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill-image-resize-module@3.0.0/image-resize.min.js"></script>
    <?php if ($userStatus == 16): ?>
        <script>
        if (!Quill.import('modules/imageResize')) {
            Quill.register('modules/imageResize', ImageResize.default);
        }
            function selectLocalImage(quillInstance) {
                const input = document.createElement('input');
                input.setAttribute('type', 'file');
                input.setAttribute('accept', 'image/png, image/jpeg, image/webp');
                input.click();

                input.onchange = () => {
                    const file = input.files[0];
                    if (file && /^image\/(png|jpe?g|webp)$/.test(file.type)) {
                        const formData = new FormData();
                        formData.append('image', file);
                        formData.append('action', 'upload_image');

                        fetch('news_handler.php', {
                            method: 'POST',
                            body: formData
                        })
                            .then(response => response.json())
                            .then(result => {
                                if (result.url) {
                                    const range = quillInstance.getSelection();
                                    quillInstance.insertEmbed(range.index, 'image', result.url);
                                } else {
                                    alert('Upload failed: ' + (result.error || 'Unknown error'));
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('An error occurred during upload.');
                            });
                    } else if (file) {
                        alert('Only WebP, PNG, and JPG/JPEG formats are allowed.');
                    }
                };
            }

            var quill = new Quill('#editor-container', {
                theme: 'snow',
                placeholder: 'Compose an epic announcement...',
                modules: {
                    imageResize: {},
                    toolbar: {
                        container: [
                            [{ 'header': [1, 2, 3, false] }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ 'color': [] }],
                            [{ 'align': [] }],
                            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                            ['link', 'image'],
                            ['clean']
                        ],
                        handlers: {
                            image: function () {
                                selectLocalImage(this.quill);
                            }
                        }
                    }
                }
            });

            // Limit characters for Create News
            quill.on('text-change', function () {
                if (quill.getLength() > 2000) {
                    quill.deleteText(2000, quill.getLength());
                }
            });

            let modalQuill = null;
            function editNews(id) {
                const article = document.querySelector(`.news-article[data-id="${id}"]`);
                const title = article.querySelector('h2').childNodes[0].textContent.trim();
                const content = document.getElementById('raw-content-' + id).textContent.trim();

                document.getElementById('modal-news-id').value = id;
                document.getElementById('modal-news-title').value = title;

                document.getElementById('editNewsModal').style.display = 'flex';
                document.body.style.overflow = 'hidden';

                if (!modalQuill) {
                    modalQuill = new Quill('#modal-editor-container', {
                        theme: 'snow',
                        modules: {
                            imageResize: {},
                            toolbar: {
                                container: [
                                    [{ 'header': [1, 2, 3, false] }],
                                    ['bold', 'italic', 'underline', 'strike'],
                                    [{ 'color': [] }],
                                    [{ 'align': [] }],
                                    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                                    ['link', 'image'],
                                    ['clean']
                                ],
                                handlers: {
                                    image: function () {
                                        selectLocalImage(this.quill);
                                    }
                                }
                            }
                        }
                    });

                    modalQuill.on('text-change', function () {
                        if (modalQuill.getLength() > 2000) {
                            modalQuill.deleteText(2000, modalQuill.getLength());
                        }
                    });
                }

                modalQuill.root.innerHTML = content;
            }

            function cancelEdit() {
                document.getElementById('editNewsModal').style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        </script>

        <!-- Edit News Modal -->
        <div id="editNewsModal" class="custom-modal-overlay">
            <div class="custom-modal-content">
                <div class="custom-modal-header">
                    <h2>Edit News Post</h2>
                    <button type="button" class="custom-modal-close" onclick="cancelEdit()">&times;</button>
                </div>
                <form action="news_handler.php" method="POST" id="modal-edit-form">
                    <div class="custom-modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="news_id" id="modal-news-id">
                        <div style="margin-bottom: 20px;">
                            <label
                                style="display: block; color: var(--text-gold); font-family: var(--heading-font); font-size: 12px; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 1px;">News
                                Title</label>
                            <input type="text" name="title" id="modal-news-title" required maxlength="45"
                                style="width: 100%; padding: 12px; background: rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.1); color: #fff; border-radius: 4px; font-family: inherit; outline: none; font-size: 14px;">
                        </div>
                        <div>
                            <label
                                style="display: block; color: var(--text-gold); font-family: var(--heading-font); font-size: 12px; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 1px;">Content</label>
                            <div id="modal-editor-container"
                                style="height: 450px; background: #fff; color: #000; border-radius: 4px;"></div>
                            <input type="hidden" name="content" id="modal-hidden-content">
                        </div>
                    </div>
                    <div class="custom-modal-footer">
                        <button type="button" class="btn-small" onclick="cancelEdit()"
                            style="background: rgba(255,255,255,0.05); color: #aaa; border: 1px solid rgba(255,255,255,0.1); padding: 10px 20px;">Cancel</button>
                        <button type="submit" class="btn-primary"
                            style="background: linear-gradient(135deg, #e8c881, #b1935e); border: none; padding: 10px 30px; color: #111; font-weight: bold; border-radius: 4px; cursor: pointer; text-transform: uppercase; font-size: 13px;"
                            onclick="document.getElementById('modal-hidden-content').value = modalQuill.root.innerHTML;">Save
                            Changes</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
    <script>
        (function () {
            var serverTimeEl = document.getElementById('server-time');
            var grbTimerEl = document.getElementById('grb-timer');
            if (!serverTimeEl || !grbTimerEl) return;

            // Initialize with Server's Sofia Time
            var svrYear = <?php echo date('Y'); ?>;
            var svrMonth = <?php echo date('n') - 1; ?>; // 0-11
            var svrDay = <?php echo date('j'); ?>;
            var svrHour = <?php echo date('H'); ?>;
            var svrMin = <?php echo date('i'); ?>;
            var svrSec = <?php echo date('s'); ?>;

            var svrDate = new Date(svrYear, svrMonth, svrDay, svrHour, svrMin, svrSec);
            var startTime = Date.now();

            function updateTimers() {
                var elapsed = Date.now() - startTime;
                var currentSvrDate = new Date(svrDate.getTime() + elapsed);

                // 1. Update Server Clock
                var hours = currentSvrDate.getHours();
                var mins = currentSvrDate.getMinutes();
                var secs = currentSvrDate.getSeconds();
                serverTimeEl.textContent =
                    (hours < 10 ? '0' : '') + hours + ':' +
                    (mins < 10 ? '0' : '') + mins + ':' +
                    (secs < 10 ? '0' : '') + secs;

                // 2. Update GRB Timer (Target: Sunday 20:00)
                var targetSvrDate = new Date(currentSvrDate);

                // Get days until next Sunday
                var day = currentSvrDate.getDay(); // 0 (Sun) to 6 (Sat)
                var daysUntilSunday = (7 - day) % 7;

                targetSvrDate.setDate(currentSvrDate.getDate() + daysUntilSunday);
                targetSvrDate.setHours(20, 0, 0, 0);

                // If already Sunday and past 20:00, move to NEXT Sunday
                if (day === 0 && (currentSvrDate.getHours() > 20 || (currentSvrDate.getHours() === 20 && currentSvrDate.getMinutes() > 0))) {
                    targetSvrDate.setDate(targetSvrDate.getDate() + 7);
                }

                var diff = targetSvrDate - currentSvrDate;
                var d = Math.floor(diff / (1000 * 60 * 60 * 24));
                var hh = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var mm = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                var ss = Math.floor((diff % (1000 * 60)) / 1000);

                grbTimerEl.textContent = d + "d " +
                    (hh < 10 ? '0' : '') + hh + "h " +
                    (mm < 10 ? '0' : '') + mm + "m " +
                    (ss < 10 ? '0' : '') + ss + "s";
            }
            setInterval(updateTimers, 1000);
            updateTimers();
        })();
    </script>
</body>

</html>
