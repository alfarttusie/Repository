class Install {
  sendRequest(data) {
    return new Promise((resolve, reject) => {
      const http = new XMLHttpRequest();
      http.open("POST", "php/install.php", true);
      http.setRequestHeader("Content-type", "application/json");

      if (localStorage.getItem("Bearer")) {
        http.setRequestHeader("Bearer", localStorage.getItem("Bearer"));
      }

      http.onload = () => {
        try {
          const bearerToken = http.getResponseHeader("Bearer");
          if (bearerToken) {
            localStorage.setItem("Bearer", bearerToken);
          }

          try {
            const response = JSON.parse(http.responseText);
            resolve(response);
          } catch (error) {
            resolve({ error: "Invalid JSON response" });
          }
        } catch (error) {
          resolve({ error: "Bad response" });
        }
      };

      http.onerror = () => resolve({ error: "Server is unreachable" });

      http.send(JSON.stringify(data));
    });
  }
  ShowMsg(Text) {
    const oldMsg = document.querySelector(".Msg");
    if (oldMsg) oldMsg.remove();
    const Msg = document.createElement("div");
    Msg.classList.add("Msg");
    Msg.style.background = "rgba(255, 0, 0, 0.56)";
    document.body.appendChild(Msg);
    Msg.textContent = Text;
  }
  constructor() {
    let form = document.querySelector("form");

    form.onsubmit = (event) => {
      event.preventDefault();
      console.log();

      let db_username = document.querySelector(".db_username").value.trim();
      let db_password = document.querySelector(".db_password").value.trim();
      let db_name = document.querySelector(".db_name").value.trim();
      let username = document.querySelector(".username").value.trim();
      let Password = document.querySelector(".Password").value.trim();
      const type = event.submitter.value;

      if (!db_username || !db_name) {
        alert("Please fill in all required fields!");
        return;
      }
      let installButton = document.querySelector(".install-button");
      installButton.disabled = true;
      installButton.textContent = "Installing...";
      this.sendRequest({
        type,
        db_username,
        db_password,
        db_name,
        username,
        Password,
      }).then((callback) => {
        installButton.disabled = false;
        installButton.textContent = "Install";
        if (callback.success) {
          alert("Installation completed successfully!");
          window.location.href = callback.redirect || "index.php";
        } else {
          ShowMsg(callback.error);
        }
      });
    };
  }
}

new Install();
