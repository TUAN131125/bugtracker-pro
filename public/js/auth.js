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
