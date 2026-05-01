// ── Global Search shortcut Ctrl+K ──
document.addEventListener("keydown", function (e) {
  if ((e.ctrlKey || e.metaKey) && e.key === "k") {
    e.preventDefault();
    const searchInput = document.getElementById("globalSearch");
    if (searchInput) searchInput.focus();
  }
});

// ── Tự động ẩn flash message sau 4 giây ──
document.querySelectorAll(".alert").forEach(function (alert) {
  setTimeout(function () {
    alert.classList.remove("show");
    alert.classList.add("fade");
  }, 4000);
});

// ── Xác nhận trước khi thực hiện action nguy hiểm ──
document.querySelectorAll("[data-confirm]").forEach(function (el) {
  el.addEventListener("click", function (e) {
    if (!confirm(el.getAttribute("data-confirm"))) {
      e.preventDefault();
    }
  });
});

// ── Global Search realtime ──
const searchInput = document.getElementById("globalSearch");
const searchResults = document.getElementById("searchResults");

let searchTimer = null;

if (searchInput && searchResults) {
  searchInput.addEventListener("input", function () {
    clearTimeout(searchTimer);
    const q = this.value.trim();

    if (q.length < 2) {
      searchResults.classList.add("d-none");
      searchResults.innerHTML = "";
      return;
    }

    searchTimer = setTimeout(async () => {
      try {
        const res = await fetch(
          `${APP_URL}/api/search?q=${encodeURIComponent(q)}`,
        );
        const data = await res.json();
        renderSearchResults(data);
      } catch (e) {
        console.error("Search error:", e);
      }
    }, 300); // Debounce 300ms
  });

  searchInput.addEventListener("focus", function () {
    if (this.value.trim().length >= 2) {
      searchResults.classList.remove("d-none");
    }
  });
}

function renderSearchResults(data) {
  if (!searchResults) return;

  const { issues = [], projects = [], users = [] } = data;
  const total = issues.length + projects.length + users.length;

  if (total === 0) {
    searchResults.innerHTML = `
            <div class="p-3 text-center text-muted" style="font-size:13px;">
                <i class="fa fa-search me-1"></i>
                Không tìm thấy kết quả cho "<strong>${escapeHtml(data.query)}</strong>"
            </div>`;
    searchResults.classList.remove("d-none");
    return;
  }

  let html = "";

  // Issues
  if (issues.length > 0) {
    html += `<div class="px-3 py-1 bg-light border-bottom">
                    <small class="text-muted fw-bold" style="font-size:11px;letter-spacing:.5px;">
                        ISSUES
                    </small>
                 </div>`;
    issues.forEach((issue) => {
      const statusColors = {
        open: "#0078D4",
        in_progress: "#FD7E14",
        review: "#6A1B9A",
        resolved: "#28A745",
        closed: "#6C757D",
      };
      const color = statusColors[issue.status] || "#6C757D";
      html += `
                <a href="${APP_URL}/issues/${escapeHtml(issue.issue_key)}"
                   class="d-flex align-items-center gap-2 px-3 py-2
                          text-decoration-none border-bottom search-item">
                    <span class="fw-bold text-primary"
                          style="font-family:monospace;font-size:12px;min-width:70px;">
                        ${escapeHtml(issue.issue_key)}
                    </span>
                    <span class="text-dark text-truncate" style="font-size:13px;flex:1;">
                        ${escapeHtml(issue.title)}
                    </span>
                    <span class="badge rounded-pill"
                          style="background:${color}22;color:${color};font-size:10px;">
                        ${issue.status}
                    </span>
                </a>`;
    });
  }

  // Projects
  if (projects.length > 0) {
    html += `<div class="px-3 py-1 bg-light border-bottom border-top">
                    <small class="text-muted fw-bold" style="font-size:11px;letter-spacing:.5px;">
                        DỰ ÁN
                    </small>
                 </div>`;
    projects.forEach((proj) => {
      html += `
                <a href="${APP_URL}/projects/${proj.key.toLowerCase()}"
                   class="d-flex align-items-center gap-2 px-3 py-2
                          text-decoration-none border-bottom search-item">
                    <div style="width:28px;height:28px;border-radius:6px;
                                background:#E3F2FD;display:flex;align-items:center;
                                justify-content:center;font-size:11px;
                                font-weight:800;color:#0078D4;flex-shrink:0;">
                        ${escapeHtml(proj.key.substring(0, 2))}
                    </div>
                    <span class="text-dark" style="font-size:13px;">
                        ${escapeHtml(proj.name)}
                    </span>
                    <span class="badge bg-light text-dark border ms-auto"
                          style="font-family:monospace;font-size:10px;">
                        ${escapeHtml(proj.key)}
                    </span>
                </a>`;
    });
  }

  // Users
  if (users.length > 0) {
    html += `<div class="px-3 py-1 bg-light border-bottom border-top">
                    <small class="text-muted fw-bold" style="font-size:11px;letter-spacing:.5px;">
                        THÀNH VIÊN
                    </small>
                 </div>`;
    users.forEach((user) => {
      const avatar = user.avatar
        ? `<img src="${APP_URL}/uploads/${escapeHtml(user.avatar)}"
                        style="width:28px;height:28px;border-radius:50%;object-fit:cover;">`
        : `<div style="width:28px;height:28px;border-radius:50%;
                               background:#0078D4;color:#fff;font-size:12px;
                               font-weight:700;display:flex;align-items:center;
                               justify-content:center;">
                       ${escapeHtml(user.full_name.charAt(0).toUpperCase())}
                   </div>`;
      html += `
                <div class="d-flex align-items-center gap-2 px-3 py-2 border-bottom search-item">
                    ${avatar}
                    <div>
                        <div style="font-size:13px;font-weight:600;">
                            ${escapeHtml(user.full_name)}
                        </div>
                        <div class="text-muted" style="font-size:11px;">
                            @${escapeHtml(user.username)} · ${escapeHtml(user.role)}
                        </div>
                    </div>
                </div>`;
    });
  }

  searchResults.innerHTML = html;
  searchResults.classList.remove("d-none");
}

function escapeHtml(str) {
  const div = document.createElement("div");
  div.appendChild(document.createTextNode(str || ""));
  return div.innerHTML;
}
