class index {
  constructor() {
    document.addEventListener("DOMContentLoaded", () => {
      indicatorRemover();
      const inputs = document.querySelectorAll("input");
      inputs.forEach((input) => {
        input.addEventListener("input", (event) => {
          changeDirection(event.target);
        });
      });
      this.initializeSession();
    });
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
        sendRequest({
          type: "sign in",
          username: encodedUsername,
          password: encodedPassword,
        }).then((response) => {
          Button.classList.remove("disabled");
          switch (response.response) {
            case "blocked":
              ShowMsg(`وقت الحضر ${response.time} ثانية`);
              break;
            case "ok":
              localStorage.token = response.token;
              window.location = "home.php";
              break;
            case "wrong":
              ShowMsg(`المعلومات غير صحيحة`);
              break;
            default:
              ShowMsg(`لا يمكن تسجيل الدخول`);
              break;
          }
        });
      } catch (err) {
        console.log(err.message);
      }
    } catch (error) {
      ShowMsg(`لا تترك حقل فارغ`);
      Shake(error.message);
    }
  }
  initializeSession() {
    Showindicator(document.body);
    sendRequest({ type: "init session" }).then((response) => {
      indicatorRemover({ element: ".login-holder", type: "grid" });
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
      else responseHandler(response.status);
    });
  }
}
new index();
