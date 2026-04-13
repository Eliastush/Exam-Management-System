    </div> <!-- main-content -->
</div> <!-- dashboard-container -->

<!-- Footer -->
<footer class="footer">
    <div class="footer-content footer-grid">
        <div class="footer-brand">
            <span>Mustard Seed ICT</span>
            <span class="beta-label">Beta</span>
        </div>
        <div class="footer-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="reports.php">Reports</a>
            <a href="settings.php">Settings</a>
        </div>
        <div>
            <span>Designed for fast school analytics and modern classroom workflows.</span>
        </div>
    </div>
</footer>

<script>
// ====================== SIDEBAR TOGGLE ======================
function toggleSidebar() {
    const sidebar = document.getElementById("sidebar");
    const mainContent = document.querySelector(".main-content");
    const footer = document.querySelector('.footer');

    if (sidebar && mainContent) {
        sidebar.classList.toggle("collapsed");
        mainContent.classList.toggle("expanded");
        if (footer) {
            footer.classList.toggle('expanded');
        }
    }
}

// ====================== GLOBAL LIVE SEARCH ======================
const globalSearchInput = document.getElementById('globalSearch');
const globalResultsContainer = document.getElementById('searchResults');
let globalSearchTimeout = null;

if (globalSearchInput && globalResultsContainer) {
    globalSearchInput.addEventListener('input', function() {
        clearTimeout(globalSearchTimeout);
        
        const query = this.value.trim();
        
        if (query.length < 2) {
            globalResultsContainer.classList.remove('show');
            return;
        }

        globalResultsContainer.innerHTML = `<div class="no-results">Searching...</div>`;
        globalResultsContainer.classList.add('show');

        globalSearchTimeout = setTimeout(() => {
            fetch('includes/search_global.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ query: query })
            })
            .then(res => res.json())
            .then(data => {
                globalResultsContainer.innerHTML = '';
                
                if (data.status === 'success' && data.results && data.results.length > 0) {
                    data.results.forEach(item => {
                        const div = document.createElement('a');
                        div.href = item.url || '#';
                        div.className = 'search-item';
                        div.innerHTML = `
                            <i class="${item.icon || 'fas fa-search'}"></i>
                            <div style="flex:1">
                                <div class="title">${item.title}</div>
                                <div class="subtitle">${item.subtitle}</div>
                            </div>
                            <span class="type-badge">${item.type}</span>
                        `;
                        globalResultsContainer.appendChild(div);
                    });
                } else {
                    globalResultsContainer.innerHTML = `<div class="no-results">No results found for "<strong>${query}</strong>"</div>`;
                }
            })
            .catch(() => {
                globalResultsContainer.innerHTML = `<div class="no-results">Search error. Please try again.</div>`;
            });
        }, 280);
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!globalSearchInput.contains(e.target) && !globalResultsContainer.contains(e.target)) {
            globalResultsContainer.classList.remove('show');
        }
    });
}

// Keyboard shortcut: Press "/" to focus global search
document.addEventListener('keydown', function(e) {
    if (e.key === '/' && 
        document.activeElement.tagName !== "INPUT" && 
        document.activeElement.tagName !== "TEXTAREA") {
        e.preventDefault();
        if (globalSearchInput) globalSearchInput.focus();
    }
});
</script>

</body>
</html>