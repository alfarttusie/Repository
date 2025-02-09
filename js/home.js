class home {
  static get WorkDiv() {
    const Holder = document.querySelector(".left");
    Holder.innerHTML = "";
    let Work = document.createElement("div");
    Work.classList.add("WorkDiv");
    Holder.appendChild(Work);
    return Work;
  }
  constructor() {
    document.addEventListener("DOMContentLoaded", () => {
      indicatorRemover();
      new Key();
      /** Event Listener */
      {
        document.querySelector("#logout").onclick = this.logOut;
        document.querySelector("#home").onclick = () =>
          (window.location = "home.php");
      }
    });
  }
  logOut() {
    const userConfirmation = confirm("هل تريد تسجيل الخروج؟");
    if (userConfirmation) {
      sendRequest({ type: "log out" }).then(() => {
        window.location = "index.php";
        localStorage.key = null;
      });
    }
  }
  static GetButtons() {
    Showindicator(document.querySelector(".right"));
    const HolderDiv = document.querySelector(".right");
    sendRequest({ type: "queries", job: "buttons list" }).then((callback) => {
      indicatorRemover();
      if (callback.response == "empty") {
        const p = document.createElement("p");
        p.classList.add("empty");
        p.innerText = "لا توجد معلومات";
        HolderDiv.appendChild(p);
      } else if (callback.response == "ok") {
        HolderDiv.innerHTML = "";
        callback.buttons.forEach((button) => {
          const buttonElement = elementCreator({
            parent: HolderDiv,
            type: "button",
            params: {
              innerText: button,
              onclick: (event) => new ShowButton(event),
              oncontextmenu: (e) => ContextMenuHandler.Start(e, buttonElement),
              ontouchstart: (e) => ContextMenuHandler.Start(e, buttonElement),
              ontouchend: (e) => ContextMenuHandler.HandleTouchEnd?.(e),
            },
          });
        });
      }
    });
  }
}

new home();
