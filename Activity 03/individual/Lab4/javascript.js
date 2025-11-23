const loginBtn = document.getElementById("loginB");
const signUpBtn = document.getElementById("sign-UpB");
const loginForm = document.getElementById("Login");
const signUpForm = document.getElementById("Sign-Up");

if (signUpBtn && loginBtn) {
  loginForm.style.display = "flex";
  signUpForm.style.display = "none";

  signUpBtn.addEventListener("click", () => {
    signUpBtn.classList.add("active");
    loginBtn.classList.remove("active");
    signUpForm.style.display = "flex";
    loginForm.style.display = "none";
  });

  loginBtn.addEventListener("click", () => {
    loginBtn.classList.add("active");
    signUpBtn.classList.remove("active");
    loginForm.style.display = "flex";
    signUpForm.style.display = "none";
  });
}

//taking input
if (signUpForm) {
  signUpForm.addEventListener("submit", (e) => {
    e.preventDefault();

    const name = signUpForm.querySelector('input[placeholder="Full name"]').value.trim();
    const email = signUpForm.querySelector('input[placeholder="Email"]').value.trim();
    const password = signUpForm.querySelector('input[placeholder="Password"]').value.trim();

    if (!name || !email || !password) {
      alert("Please fill all fields.");
      return;
    }

    const userData = { name, email, password };
    localStorage.setItem("fi_user", JSON.stringify(userData));

    alert("Sign-Up successful! Please log in.");
    signUpForm.reset();
    loginBtn.click(); // Switch back to login form
  });
}

if (loginForm) {
  loginForm.addEventListener("submit", (e) => {
    e.preventDefault();

    const email = loginForm.querySelector('input[placeholder="Email"]').value.trim();
    const password = loginForm.querySelector('input[placeholder="Password"]').value.trim();
    const storedUser = JSON.parse(localStorage.getItem("fi_user"));

    if (!storedUser) {
      alert("No user registered yet. Please sign up first.");
      return;
    }

    if (email === storedUser.email && password === storedUser.password) {
      alert(`Welcome, ${storedUser.name}!`);
      localStorage.setItem("fi_loggedIn", "true");
      window.location.href = "dashboard.html";
    } else {
      alert("Incorrect email or password.");
    }
  });
}

//logout
const logoutBtn = document.querySelector(".logout-btn");
if (logoutBtn) {
  logoutBtn.addEventListener("click", () => {
    localStorage.removeItem("fi_loggedIn");
    window.location.href = "index.html";
  });
}
