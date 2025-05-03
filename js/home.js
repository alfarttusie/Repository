class home {
  static get WorkDiv() {
    const Holder = document.querySelector(".left");
    Holder.innerHTML = "";
    const Work = document.createElement("div");
    Work.classList.add("WorkDiv");
    Holder.appendChild(Work);
    return Work;
  }

  constructor() {
    document.addEventListener("DOMContentLoaded", () => {
      indicatorRemover();
      new Key();
      this.bindEvents();
    });
  }

  bindEvents() {
    const logoutBtn = document.querySelector("#logout");
    const homeBtn = document.querySelector("#home");
    const settings = document.querySelector("#settings");

    if (logoutBtn) logoutBtn.onclick = () => this.logOut();
    if (homeBtn) homeBtn.onclick = () => (window.location = "home.php");
    if (settings) settings.onclick = () => (window.location = "settings.php");
    document.querySelector("#lang-btn").onclick = (e) => {
      const lang = e.target.innerText.toLowerCase().includes("english")
        ? "en"
        : "ar";
      location.href = `home.php?lang=${lang}`;
    };
  }

  logOut() {
    const confirmed = confirm(lang.get("logout-confirm"));
    if (!confirmed) return;

    sendRequest({ type: "log out" }).then(() => {
      localStorage.removeItem("bearer");
      window.location = "index.php";
    });
  }

  static GetButtons() {
    const RightDiv = document.querySelector(".right");
    Showindicator(RightDiv);

    sendRequest({ type: "queries", job: "buttons list" }).then((callback) => {
      indicatorRemover();

      if (callback.response === "empty") {
        const EmptyP = elementCreator({
          parent: RightDiv,
          type: "p",
          params: {
            classList: ["empty"],
            innerText: "لا توجد معلومات",
          },
        });
        return;
      }

      if (callback.response === "ok") {
        RightDiv.innerHTML = "";
        callback.buttons.forEach((button) => {
          const buttonElement = elementCreator({
            parent: RightDiv,
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

  destroy() {
    const logoutBtn = document.querySelector("#logout");
    const homeBtn = document.querySelector("#home");

    if (logoutBtn) logoutBtn.onclick = null;
    if (homeBtn) homeBtn.onclick = null;
  }
}

new home();
