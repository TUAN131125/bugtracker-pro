// ── Password strength indicator ──
const pwInput = document.getElementById("password");
const pwBar = document.getElementById("passwordStrength");

if (pwInput && pwBar) {
  pwInput.addEventListener("input", function () {
    const val = this.value;
    let score = 0;
    if (val.length >= 8) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const colors = ["bg-danger", "bg-warning", "bg-info", "bg-success"];
    const labels = ["Yếu", "Trung bình", "Khá", "Mạnh"];

    pwBar.style.width = score * 25 + "%";
    pwBar.className = "progress-bar " + (colors[score - 1] || "bg-danger");
    const label = document.getElementById("pwStrengthLabel");
    if (label) label.textContent = labels[score - 1] || "";
  });
}

// ── Validate confirm password realtime ──
const pw2 = document.getElementById("password_confirm");
if (pw2) {
  pw2.addEventListener("input", function () {
    const match = this.value === (pwInput?.value ?? "");
    this.classList.toggle("is-invalid", !match);
    this.classList.toggle("is-valid", match && this.value.length > 0);
  });
}

// ════════════════════════════════════
// Auth Page JavaScript Helpers
// ════════════════════════════════════

document.addEventListener("DOMContentLoaded", function () {
  // ── PASSWORD STRENGTH ──
  initPasswordStrength();

  // ── SHOW/HIDE PASSWORD ──
  initPasswordToggle();

  // ── REALTIME EMAIL CHECK ──
  initEmailCheck();

  // ── REALTIME USERNAME CHECK ──
  initUsernameCheck();

  // ── SLUG GENERATOR ──
  initSlugGenerator();

  // ── FORM VALIDATION ──
  initFormValidation();

  // ── PASSWORD CONFIRM MATCH ──
  initPasswordConfirm();
});

// ── PASSWORD STRENGTH ──
function initPasswordStrength() {
  const pwInput = document.getElementById("password");
  if (!pwInput) return;

  pwInput.addEventListener("input", function () {
    const val = this.value;
    let score = 0;

    if (val.length >= 8) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const pwBar = document.getElementById("passwordStrength");
    const label = document.getElementById("pwStrengthLabel");

    if (pwBar) {
      const percent = (score / 4) * 100;
      pwBar.style.width = percent + "%";

      const colors = ["bg-danger", "bg-warning", "bg-info", "bg-success"];
      pwBar.className = "progress-bar " + (colors[score - 1] || "bg-danger");
    }

    if (label) {
      const labels = ["Yếu", "Trung bình", "Khá", "Mạnh"];
      label.textContent = labels[score - 1] || "Yêu cầu: 8+ ký tự, chữ hoa, số";
    }
  });
}

// ── SHOW/HIDE PASSWORD ──
function initPasswordToggle() {
  document.querySelectorAll('[id^="togglePw"]').forEach((btn) => {
    btn.addEventListener("click", function () {
      const target = this.dataset.target || "password";
      const input = document.getElementById(target);
      if (!input) return;

      const isPassword = input.type === "password";
      input.type = isPassword ? "text" : "password";
      this.innerHTML = isPassword
        ? '<i class="fa fa-eye-slash"></i>'
        : '<i class="fa fa-eye"></i>';
    });
  });
}

// ── REALTIME EMAIL CHECK ──
function initEmailCheck() {
  const emailInput = document.getElementById("emailInput");
  if (!emailInput) return;

  emailInput.addEventListener("blur", async function () {
    const email = this.value.trim();
    if (!email) return;

    try {
      const res = await fetch(
        `<?= APP_URL ?>/api/check-email?email=${encodeURIComponent(email)}`,
      );
      const data = await res.json();

      if (data.available) {
        this.classList.remove("is-invalid");
        this.classList.add("is-valid");
      } else {
        this.classList.remove("is-valid");
        this.classList.add("is-invalid");
        if (!this.nextElementSibling?.classList.contains("invalid-feedback")) {
          const err = document.createElement("div");
          err.className = "invalid-feedback d-block";
          err.innerHTML =
            '<i class="fa fa-times-circle me-1"></i>Email này đã được dùng';
          this.parentNode.insertBefore(err, this.nextSibling);
        }
      }
    } catch (e) {
      console.error("Check email error:", e);
    }
  });
}

// ── REALTIME USERNAME CHECK ──
function initUsernameCheck() {
  const usernameInput = document.getElementById("usernameInput");
  if (!usernameInput) return;

  usernameInput.addEventListener("blur", async function () {
    const username = this.value.trim();
    if (!username) return;

    try {
      const res = await fetch(
        `<?= APP_URL ?>/api/check-username?username=${encodeURIComponent(username)}`,
      );
      const data = await res.json();

      const statusSpan = document.getElementById("usernameStatus");
      if (statusSpan) {
        if (data.available) {
          statusSpan.innerHTML = '<i class="fa fa-check text-success"></i>';
          this.classList.remove("is-invalid");
          this.classList.add("is-valid");
        } else {
          statusSpan.innerHTML = '<i class="fa fa-times text-danger"></i>';
          this.classList.remove("is-valid");
          this.classList.add("is-invalid");
        }
      }

      if (data.errors?.length) {
        // Hiện errors validation
        const errDiv = document.createElement("div");
        errDiv.className = "text-danger small mt-1";
        errDiv.innerHTML = data.errors
          .map((e) => `<i class="fa fa-exclamation me-1"></i>${e}`)
          .join("<br>");
        this.parentNode.appendChild(errDiv);
      }
    } catch (e) {
      console.error("Check username error:", e);
    }
  });
}

// ── SLUG GENERATOR ──
function initSlugGenerator() {
  const wsNameInput = document.getElementById("wsNameInput");
  if (!wsNameInput) return;

  wsNameInput.addEventListener("input", function () {
    const name = this.value.trim();
    if (name) {
      const slug = name
        .toLowerCase()
        .replace(/\s+/g, "-")
        .replace(/[^a-z0-9\-]/g, "");

      const slugInput = document.getElementById("wsSlugInput");
      if (slugInput) slugInput.value = slug;
    }
  });
}

// ── PASSWORD CONFIRM MATCH ──
function initPasswordConfirm() {
  const pwConfirm = document.getElementById("password_confirm");
  const pwInput = document.getElementById("password");

  if (!pwConfirm || !pwInput) return;

  pwConfirm.addEventListener("input", function () {
    const match = this.value === pwInput.value;
    this.classList.toggle("is-invalid", !match && this.value.length > 0);
    this.classList.toggle("is-valid", match && this.value.length > 0);
  });
}

// ── FORM VALIDATION ──
function initFormValidation() {
  const forms = document.querySelectorAll("form[novalidate]");

  forms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      // Tạm thời chỉ dùng browser validation
      // Chi tiết validation được làm ở backend (Dev A)
    });
  });
}
