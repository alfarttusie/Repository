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
      // new AddButton();
      // Key.checker();
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
        callback.buttons.forEach((button) => {
          const buttonElement = elementCreator({
            parent: HolderDiv,
            type: "button",
            params: {
              innerText: button,
              onclick: home.ShowButton,
              oncontextmenu: (e) => ContextMenuHandler.Start(e, buttonElement),
              ontouchstart: (e) => ContextMenuHandler.Start(e, buttonElement),
              ontouchend: (e) => ContextMenuHandler.HandleTouchEnd?.(e),
            },
          });
        });
      }
    });
  }
  static ShowButton(event) {
    const ButtonName = event?.target?.innerText ?? event;

    sendRequest({
      type: "queries",
      job: "show Button",
      button: ButtonName,
    }).then((callback) => {
      const leftDiv = document.querySelector(".left");
      leftDiv.innerHTML = "";
      const HolderDiv = elementCreator({
        parent: leftDiv,
        type: "div",
        params: { className: "HolderDiv" },
      });
      callback.data.forEach((data) => {
        console.log(data);
        const line = elementCreator({
          parent: HolderDiv,
          type: "div",
          params: { className: "line", id: data.id },
        });
        const main = elementCreator({ parent: line, type: "p", parent: line });
        Object.keys(data.main).forEach((b) => {
          const label = b;
          const value = data.main[b];
          elementCreator({
            parent: main,
            type: "span",
            params: { innerText: label },
          });
          elementCreator({
            parent: main,
            type: "span",
            params: { innerText: value },
          });
        });
        const passwords = elementCreator({
          parent: line,
          type: "p",
          parent: line,
        });
        Object.keys(data.passwords).forEach((b) => {
          const label = b;
          const value = data.passwords[b];
          elementCreator({
            parent: passwords,
            type: "span",
            params: { innerText: label },
          });
          elementCreator({
            parent: passwords,
            type: "span",
            params: { innerText: value },
          });
        });
        elementCreator({
          parent: line,
          type: "button",
          params: {
            innerText: "نفاصيل",
            id: data.id,
            onclick: () => {
              alert(data.id);
            },
          },
        });
      });
    });
  }
}

new home();
