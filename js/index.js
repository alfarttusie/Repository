class index {
  constructor() {
    indicatorRemover();
    const inputs = document.querySelectorAll("input");
    inputs.forEach((input) => {
      input.addEventListener("input", (event) => {
        changeDirection(event.target);
      });
    });
    this.initializeSession();
  }

  handleLogin() {
    const Button = document.querySelector(".login-btn");
    const username = document.querySelector(".username");
    const password = document.querySelector(".password");
    try {
      if (username.value.length === 0) throw new Error(".username");
      if (password.value.length === 0) throw new Error(".password");
      try {
        const encodedUsername = username.value;
        const encodedPassword = password.value;
        Button.classList.add("disabled");
        Showindicator(document.querySelector(".login-holder"));
        sendRequest({
          type: "sign in",
          username: encodedUsername,
          password: encodedPassword,
        }).then((response) => {
          indicatorRemover();
          Button.classList.remove("disabled");
          switch (response.response) {
            case "blocked":
              Message(`وقت الحضر ${response.time} ثانية`);
              Shake(".password");
              break;
            case "ok":
              window.location = "home.php";
              break;
            case "wrong":
              localStorage.removeItem("bearer");
              const attemptsLeft =
                response.attemptsLeft == "none" ? 0 : response.attemptsLeft;
              Message(`المعلومات غير صحيحة محاولات متبقية ${attemptsLeft}`);
              Shake(".username");
              break;
            default:
              Message(`لا يمكن تسجيل الدخول`);
              break;
          }
        });
      } catch (err) {
        console.log(err.message);
      }
    } catch (error) {
      Message(`لا تترك حقل فارغ`);
      Shake(error.message);
    }
  }
  initializeSession() {
    Showindicator(document.querySelector(".login-holder"));

    sendRequest({ type: "init session" }).then((response) => {
      indicatorRemover({ element: ".login-holder" });
      if (response.status == "successful") {
        const loginBtn = document.querySelector(".login-btn");
        loginBtn.addEventListener("click", () => this.handleLogin());
        document.addEventListener(
          "keydown",
          (event) => event.key === "Enter" && loginBtn.click()
        );
        document
          .querySelector(".view-password")
          .addEventListener("click", ShowPassword);
      } else if (response.status === "logedin") window.location = "home.php";
    });
  }
}

document.addEventListener("DOMContentLoaded", () => {
  new index();
});
