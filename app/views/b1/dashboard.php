<?php
$title = 'B1 Dashboard';
$user = $user ?? currentUser();
?>
<div class="reactive-bg"></div>

<nav class="navbar fade-in">
    <a href="<?php echo url('home'); ?>" class="logo">
        <i class="fas fa-earth-europe" style="color: var(--primary-color); margin-right: 10px;"></i>
        Vorbereitung
    </a>
    <div class="menu-container">
        <div class="nav-segment">
            <a class="btn ghost" href="<?php echo url('home'); ?>"><i class="fas fa-home"></i></a>
            <a class="btn ghost" href="<?php echo url('profile'); ?>" title="Profil"><i class="fas fa-user"></i></a>
            <a class="btn ghost" href="<?php echo url('logout'); ?>"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </div>
    <div class="nav-links">
        <a href="<?php echo url('b1'); ?>" class="btn ghost">B1</a>
        <a href="<?php echo url('b2'); ?>" class="btn ghost">B2</a>
        <?php 
        $currentUser = currentUser();
        if ($currentUser && isset($currentUser['role']) && $currentUser['role'] === 'admin'): ?>
            <a href="<?php echo url('admin'); ?>" class="btn ghost">Admin</a>
        <?php endif; ?>
    </div>
</nav>

<div class="dashboard-container fade-in" style="animation-delay: 0.1s;">
    <div class="hero">
        <a href="<?php echo url('home'); ?>" class="back"><i class="fas fa-arrow-left"></i> Zurück</a>
        <div class="title">Prüfung B1</div>
        <div class="subtitle">Quiz Practice</div>
    </div>
    
    <div class="tabs">
        <div class="tab active" data-target="tab-b1-lesen">Lesen</div>
        <div class="tab" data-target="tab-b1-gram">Sprachbausteine</div>
        <div class="tab" data-target="tab-b1-hoeren">Hören</div>
        <div class="tab" data-target="tab-b1-schreiben">Schreiben</div>
    </div>
    
    <!-- Lesen Tab -->
    <div id="tab-b1-lesen" class="tab-content active">
        <div class="sub-tabs">
            <div class="sub-tab active" data-target="lesen-b1-part1">Part 1</div>
            <div class="sub-tab" data-target="lesen-b1-part2">Part 2</div>
            <div class="sub-tab" data-target="lesen-b1-part3">Part 3</div>
        </div>

        <!-- Lesen Teil 1 -->
        <div class="section-card sub-content active" id="lesen-b1-part1">
            <div class="section-title">
                <i class="fas fa-book-open" style="color: var(--primary-color);"></i>
                Lesen Teil 1
            </div>
            <div class="quiz-grid">
                <?php foreach ($quizzes['lesen']['part1'] as $quiz): ?>
                    <a class="quiz-btn" href="<?php echo url('quiz/' . $quiz['file'] . '?level=B1'); ?>"><?php echo $quiz['name']; ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Lesen Teil 2 -->
        <div class="section-card sub-content" id="lesen-b1-part2">
            <div class="section-title">
                <i class="fas fa-book-reader" style="color: var(--secondary-color);"></i>
                Lesen Teil 2
            </div>
            <div class="quiz-grid">
                <?php foreach ($quizzes['lesen']['part2'] as $quiz): ?>
                    <a class="quiz-btn" href="<?php echo url('quiz/' . $quiz['file'] . '?level=B1'); ?>"><?php echo $quiz['name']; ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Lesen Teil 3 -->
        <div class="section-card sub-content" id="lesen-b1-part3">
            <div class="section-title">
                <i class="fas fa-newspaper" style="color: var(--primary-color);"></i>
                Lesen Teil 3
            </div>
            <div class="quiz-grid">
                <?php foreach ($quizzes['lesen']['part3'] as $quiz): ?>
                    <a class="quiz-btn" href="<?php echo url('quiz/' . $quiz['file'] . '?level=B1'); ?>"><?php echo $quiz['name']; ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Sprachbausteine Tab -->
    <div id="tab-b1-gram" class="tab-content">
        <!-- Sprachbausteine Teil 1 -->
        <div class="section-card">
            <div class="section-title">
                <i class="fas fa-puzzle-piece" style="color: var(--secondary-color);"></i>
                Sprachbausteine Teil 1
            </div>
            <div class="quiz-grid">
                <?php foreach ($quizzes['sprachbausteine']['part1'] as $quiz): ?>
                    <a class="quiz-btn" href="<?php echo url('quiz/' . $quiz['file'] . '?level=B1'); ?>"><?php echo $quiz['name']; ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Sprachbausteine Teil 2 -->
        <div class="section-card">
            <div class="section-title">
                <i class="fas fa-shapes" style="color: var(--primary-color);"></i>
                Sprachbausteine Teil 2
            </div>
            <div class="quiz-grid">
                <?php foreach ($quizzes['sprachbausteine']['part2'] as $quiz): ?>
                    <a class="quiz-btn" href="<?php echo url('quiz/' . $quiz['file'] . '?level=B1'); ?>"><?php echo $quiz['name']; ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Hören Tab -->
    <div id="tab-b1-hoeren" class="tab-content">
        <div class="section-card">
            <div class="section-title">
                <i class="fas fa-headphones" style="color: var(--secondary-color);"></i>
                Hören
            </div>
            <div class="quiz-grid">
                <?php foreach ($quizzes['hoeren'] as $quiz): ?>
                    <a class="quiz-btn" href="<?php echo url('quiz/' . $quiz['file'] . '?level=B1'); ?>"><?php echo $quiz['name']; ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Schreiben Tab -->
    <div id="tab-b1-schreiben" class="tab-content">
        <div class="section-card">
            <div class="section-title">
                <i class="fas fa-pen-nib" style="color: var(--primary-color);"></i>
                Aufgabe 1 – Formeller Brief
            </div>
            <div style="display: grid; gap: 16px;">
                <div style="color: var(--text-light); font-weight: 600;">Schreiben Sie einen formellen Brief.</div>
                <div style="background: var(--light-color); border-radius: 12px; padding: 14px; color: var(--text-color);">Thema: Sie haben ein Produkt gekauft, das nicht wie beschrieben funktioniert. Bitten Sie um Lösung oder Rückerstattung.</div>
                <textarea id="write1B1" style="width: 100%; min-height: 220px; border-radius: 12px; padding: 14px; font-size: 1rem;"></textarea>
                <div style="display:flex; align-items:center; justify-content:space-between;">
                    <div id="write1CountB1" style="font-weight:700; color: var(--text-light);">0 Wörter</div>
                    <div style="display:flex; gap:8px;">
                        <button id="write1B1Save" class="btn btn-secondary" style="padding:8px 16px;">Speichern</button>
                        <button id="write1B1Copy" class="btn" style="padding:8px 16px;">Kopieren</button>
                        <button id="write1B1Clear" class="btn btn-danger" style="padding:8px 16px;">Leeren</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="section-card">
            <div class="section-title">
                <i class="fas fa-feather-pointed" style="color: var(--secondary-color);"></i>
                Aufgabe 2 – Stellungnahme
            </div>
            <div style="display: grid; gap: 16px;">
                <div style="color: var(--text-light); font-weight: 600;">Schreiben Sie eine Stellungnahme.</div>
                <div style="background: var(--light-color); border-radius: 12px; padding: 14px; color: var(--text-color);">Thema: Sollten Hausaufgaben in der Schule reduziert werden? Begründen Sie Ihre Meinung.</div>
                <textarea id="write2B1" style="width: 100%; min-height: 220px; border-radius: 12px; padding: 14px; font-size: 1rem;"></textarea>
                <div style="display:flex; align-items:center; justify-content:space-between;">
                    <div id="write2CountB1" style="font-weight:700; color: var(--text-light);">0 Wörter</div>
                    <div style="display:flex; gap:8px;">
                        <button id="write2B1Save" class="btn btn-secondary" style="padding:8px 16px;">Speichern</button>
                        <button id="write2B1Copy" class="btn" style="padding:8px 16px;">Kopieren</button>
                        <button id="write2B1Clear" class="btn btn-danger" style="padding:8px 16px;">Leeren</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    var tabs = document.querySelectorAll('.tabs .tab');
    var panels = document.querySelectorAll('.tab-content');
    tabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            var id = tab.getAttribute('data-target');
            panels.forEach(function(p) { p.classList.toggle('active', p.id === id); });
            tabs.forEach(function(t) { t.classList.toggle('active', t === tab); });
        });
    });
    
    // Sub-tab switching for Lesen
    var lesen = document.getElementById('tab-b1-lesen');
    if (lesen) {
        var subTabs = lesen.querySelectorAll('.sub-tab');
        var subs = lesen.querySelectorAll('.sub-content');
        subTabs.forEach(function(st) {
            st.addEventListener('click', function() {
                var id = st.getAttribute('data-target');
                subs.forEach(function(c) { c.classList.toggle('active', c.id === id); });
                subTabs.forEach(function(s) { s.classList.toggle('active', s === st); });
            });
        });
    }
    
    // Word count and textarea functionality
    function updateCount(id, counterId, storageKey) {
        var ta = document.getElementById(id);
        var counter = document.getElementById(counterId);
        if (!ta || !counter) return;
        var saved = localStorage.getItem(storageKey);
        if (saved) { ta.value = saved; }
        var calc = function() {
            var text = ta.value.trim();
            var count = text ? text.split(/\s+/).filter(Boolean).length : 0;
            counter.textContent = count + ' Wörter';
            localStorage.setItem(storageKey, ta.value);
        };
        ta.addEventListener('input', calc);
        calc();
        var saveBtn = document.getElementById(id + 'Save');
        var copyBtn = document.getElementById(id + 'Copy');
        var clearBtn = document.getElementById(id + 'Clear');
        if (saveBtn) saveBtn.addEventListener('click', function() { localStorage.setItem(storageKey, ta.value); });
        if (copyBtn) copyBtn.addEventListener('click', function() { navigator.clipboard.writeText(ta.value); });
        if (clearBtn) clearBtn.addEventListener('click', function() { ta.value=''; calc(); });
    }
    updateCount('write1B1', 'write1CountB1', 'b1_write1');
    updateCount('write2B1', 'write2CountB1', 'b1_write2');
});
</script>
