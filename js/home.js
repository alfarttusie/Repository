class home {
  constructor() {
    document.addEventListener("DOMContentLoaded", () => {
      console.log(localStorage.barrier);
      indicatorRemover();
      Key.checker();
      /** Event Listener */
      {
        document.querySelector("#logout").onclick = this.logOut;
        document.querySelector("#home").onclick = () =>
          (window.location = "home.php");
        document.querySelector("#addButton").onclick = AddButton.start;
        document.querySelector("#key").onclick = Key.Start;
      }
    });
  }
  logOut() {
    const userConfirmation = confirm("هل تريد تسجيل الخروج؟");
    if (userConfirmation) {
      sendRequest({ type: "log out" }).then(
        () => (window.location = "index.php")
      );
    }
  }
  static GetButtons() {
    const HolderDiv = document.querySelector(".right");
    sendRequest({ type: "queries", job: "buttons list" }).then((callback) => {
      if (callback.response == "empty") {
        HolderDiv.innerHTML = "لا توجد ازرا";
      } else if (callback.response == "ok") {
        HolderDiv.innerHTML = "";
        callback.buttons.forEach((button) =>
          elementCreator({
            parent: HolderDiv,
            type: "button",
            params: {
              innerText: button,
              onclick: home.ShowButton,
              oncontextmenu: (e) => ContextMenuHandler.Start(e),
              ontouchstart: (e) => ContextMenuHandler.Start(e),
              ontouchend: (e) => ContextMenuHandler.HandleTouchEnd?.(e),
            },
          })
        );
      }
    });
  }
  static ShowButton(event) {
    const ButtonName = event.target.innerText;
    sendRequest({
      type: "queries",
      job: "show Button",
      button: ButtonName,
    }).then((callback) => console.log(callback));
  }
}

new home();
