class index {
  constructor() {
    indicatorRemover();
    document.querySelector(".language-btn").onclick = (e) => {
      const lang = e.target.innerText.toLowerCase().includes("english")
        ? "en"
        : "ar";
      sendRequest({ type: "lang", new: lang }).then((callback) => {
        if (callback.status === "successful") {
          location.reload();
        }
      });
    };

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
              startCountdown(
                response.time,
                document.querySelector(".login-holder")
              );
              Message(lang.get("blocked"));
              Shake(".password");
              break;
            case "ok":
              window.location = "home.php";
              break;
            case "wrong":
              if (response.attemptsLeft == "none") {
                Message(lang.get("blocked"));
              } else {
                Shake(".username");
                Message(lang.get("wrong-info") + ` ` + response.attemptsLeft);
              }
              break;
            default:
              Message(lang.get("login-error"));
              break;
          }
        });
      } catch (err) {
        console.log(err.message);
      }
    } catch (error) {
      Message(lang.get("empty"));
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
