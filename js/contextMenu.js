class ContextMenuHandler {
  static timeout;
  static lastTap = 0;

  static createContextMenu() {
    const existingMenu = document.querySelector(".context-menu");
    if (existingMenu) {
      existingMenu.remove();
    }

    const menu = document.createElement("div");
    menu.classList.add("context-menu");

    const items = {
      "اظافة بيانات": () => new InsertData(),
      Refresh: "",
      Copy: "",
      Customize: "",
      "حذف الزر": this.DeleteButton,
    };
    Object.keys(items).forEach((text) => {
      const item = document.createElement("div");
      item.classList.add("item");
      item.innerText = text;
      item.onclick = items[text];
      item.addEventListener("click", () => menu.remove());
      menu.appendChild(item);
    });

    document.body.appendChild(menu);

    document.addEventListener("click", ContextMenuHandler.HandleClick, {
      once: true,
    });

    return menu;
  }

  static Start(e, button) {
    e.preventDefault();
    const menu = this.createContextMenu();
    menu.dataset.invoker = button.innerText;

    let mouseX = e.clientX || e.touches?.[0]?.clientX || 0;
    let mouseY = e.clientY || e.touches?.[0]?.clientY || 0;
    let menuHeight = menu.getBoundingClientRect().height;
    let menuWidth = menu.getBoundingClientRect().width;
    let width = window.innerWidth;
    let height = window.innerHeight;

    if (width - mouseX <= 200) {
      menu.style.borderRadius = "5px 0 5px 5px";
      menu.style.left = width - menuWidth + "px";
      menu.style.top = mouseY + "px";
      if (height - mouseY <= 200) {
        menu.style.top = mouseY - menuHeight + "px";
        menu.style.borderRadius = "5px 5px 0 5px";
      }
    } else {
      menu.style.borderRadius = "0 5px 5px 5px";
      menu.style.left = mouseX + "px";
      menu.style.top = mouseY + "px";
      if (height - mouseY <= 200) {
        menu.style.top = mouseY - menuHeight + "px";
        menu.style.borderRadius = "5px 5px 5px 0";
      }
    }
  }

  static HandleClick(e) {
    const menu = document.querySelector(".context-menu");
    if (menu && !menu.contains(e.target)) {
      menu.remove();
    }
  }
  static DeleteButton() {
    const menu = document.querySelector(".context-menu");
    const buttonName = name ? name : menu.dataset.invoker;
    sendRequest({
      type: "queries",
      job: "delete button",
      button: buttonName,
    }).then((callback) => {
      showNotification(`تم حذف الزر`);
      home.GetButtons();
      home.WorkDiv;
    });
  }
}
