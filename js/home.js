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
      indicatorRemover({ type: "grid" });
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
      sendRequest({ type: "lang", new: lang }).then((callback) => {
        console.log(callback);
        if (callback.status === "successful") {
          location.reload();
        }
      });
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
    RightDiv.innerHTML = "";
    Showindicator(RightDiv);

    sendRequest({ type: "queries", job: "buttons list" }).then((callback) => {
      indicatorRemover();

      if (callback.response === "empty") {
        elementCreator({
          parent: RightDiv,
          type: "p",
          params: {
            classList: ["empty-buttons"],
            innerText: lang.get("no-info"),
          },
        });
        return;
      }

      if (callback.response === "ok") {
        const wrapper = elementCreator({
          parent: RightDiv,
          type: "div",
          params: { className: "buttons-wrapper" },
        });

        elementCreator({
          parent: wrapper,
          type: "input",
          params: {
            type: "text",
            autocomplete: "off",
            className: "search-buttons",
            placeholder: lang.get("search"),
            oninput: (e) => {
              const val = e.target.value.toLowerCase();
              wrapper.querySelectorAll("button").forEach((btn) => {
                const match = btn.innerText.toLowerCase().includes(val);
                btn.style.display = match ? "block" : "none";
              });
            },
          },
        });

        const scrollContainer = elementCreator({
          parent: wrapper,
          type: "div",
          params: { className: "buttons-scroll" },
        });

        callback.buttons.forEach((button) => {
          let touchTimer;
          const buttonElement = elementCreator({
            parent: scrollContainer,
            type: "button",
            params: {
              className: "left-buttons",
              innerText: button,
              onclick: (event) => new ShowButton(event),
              oncontextmenu: (e) => ContextMenuHandler.Start(e, buttonElement),
              ontouchstart: (e) => {
                touchTimer = setTimeout(() => {
                  ContextMenuHandler.Start(e, buttonElement);
                }, 600);
              },
              ontouchend: () => {
                clearTimeout(touchTimer);
              },
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
