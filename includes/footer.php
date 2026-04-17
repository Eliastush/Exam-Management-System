<footer class="footer beta-footer">
    <div class="footer-content beta-footer-content">
        <div class="footer-brand-block">
            <span class="profile-kicker">Beta V2.1 </span>
            <strong><?= htmlspecialchars($school_name) ?></strong>
          <p>The admin platform is now complete and ready for publishing. It features refined school branding, a streamlined workflow, and enhanced reporting capabilities.</p>
        </div>
        <div class="footer-links footer-beta-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="manage_students.php">Students</a>
            <a href="reports.php">Reports</a>
            <a href="settings.php">Settings</a>
            <a href="my_profile.php">Profile</a>
        </div>
        <div class="footer-meta beta-footer-meta">
            <span><?= date('Y') ?> @ <?= htmlspecialchars($school_name) ?></span>
            <span>Release track: <?= date('m') ?>/<?= date('M') ?></span>
            <span>Status: Online</span>
        </div>
    </div>
</footer>

<script>
const globalSearchInput = document.getElementById('globalSearch');
const globalSearchResults = document.getElementById('searchResults');
let globalSearchTimer = null;

if (globalSearchInput && globalSearchResults) {
    globalSearchInput.addEventListener('input', function() {
        clearTimeout(globalSearchTimer);
        const query = this.value.trim();

        if (query.length < 2) {
            globalSearchResults.classList.remove('show');
            globalSearchResults.innerHTML = '';
            return;
        }

        globalSearchResults.classList.add('show');
        globalSearchResults.innerHTML = '<div class="search-empty">Searching...</div>';

        globalSearchTimer = setTimeout(async () => {
            try {
                const response = await fetch('includes/search_global.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ query })
                });

                const data = await response.json();
                const results = Array.isArray(data.results) ? data.results : [];

                if (!results.length) {
                    globalSearchResults.innerHTML = `<div class="search-empty">No results found for "${query}".</div>`;
                    return;
                }

                globalSearchResults.innerHTML = results.map((item) => `
                    <a class="search-item" href="${item.url || '#'}">
                        <i class="${item.icon || 'fas fa-search'}"></i>
                        <span>
                            <strong>${item.title || 'Result'}</strong>
                            <small>${item.subtitle || ''}</small>
                        </span>
                        <em>${item.type || 'Item'}</em>
                    </a>
                `).join('');
            } catch (error) {
                console.error(error);
                globalSearchResults.innerHTML = '<div class="search-empty">Search is unavailable right now.</div>';
            }
        }, 220);
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === '/' && !['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) {
            event.preventDefault();
            globalSearchInput.focus();
        }
    });

    document.addEventListener('click', function(event) {
        if (!globalSearchInput.contains(event.target) && !globalSearchResults.contains(event.target)) {
            globalSearchResults.classList.remove('show');
        }
    });
}
</script>

<!-- Keyboard Shortcuts -->
<script>
document.addEventListener('keydown', function(e) {

    // Prevent default browser behavior for these shortcuts
    if (e.ctrlKey) {
        switch(e.key.toLowerCase()) {

            // Ctrl + S → Add New Student
            case 's':
                e.preventDefault();
                window.location.href = 'add_student.php';
                break;

            // Ctrl + R → View Results
            case 'r':
                e.preventDefault();
                window.location.href = 'exam_results.php';
                break;

            // Ctrl + M → Manage Students
            case 'm':
                e.preventDefault();
                window.location.href = 'manage_students.php';
                break;

            // Ctrl + Q → Questions Bank
            case 'q':
                e.preventDefault();
                window.location.href = 'ict_questions.php';
                break;

            // Ctrl + P → My Profile
            case 'p':
                e.preventDefault();
                window.location.href = 'my_profile.php';
                break;

            // Ctrl + D → Dashboard
            case 'd':
                e.preventDefault();
                window.location.href = 'dashboard.php';
                break;

            // Ctrl + A → Attendance
            case 'a':
                e.preventDefault();
                window.location.href = 'student_attendance.php';
                break;

            // Ctrl + E → Exams
            case 'e':
                e.preventDefault();
                window.location.href = 'manage_exams.php';
                break;

            // Ctrl + T → Take Attendance (if different from view)
            case 't':
                e.preventDefault();
                window.location.href = 'take_attendance.php';
                break;

            // Ctrl + H → Help / Shortcuts
            case 'h':
                e.preventDefault();
                alert("Keyboard Shortcuts:\n\n" +
                      "Ctrl + S = Add Student\n" +
                      "Ctrl + R = View Results\n" +
                      "Ctrl + M = Manage Students\n" +
                      "Ctrl + Q = Questions Bank\n" +
                      "Ctrl + P = My Profile\n" +
                      "Ctrl + D = Dashboard\n" +
                      "Ctrl + A = Attendance\n" +
                      "Ctrl + E = Exams\n" +
                      "Ctrl + T = Take Attendance\n" +
                      "Ctrl + H = Show Help");
                break;
        }
    }
});
</script>
</body>
</html>
