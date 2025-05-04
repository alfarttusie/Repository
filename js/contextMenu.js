class ContextMenuHandler {
  static timeout;
  static lastTap = 0;

  static createMenuItem(label, action, parent) {
    const item = document.createElement("div");
    item.classList.add("item");
    item.innerText = label;
    item.onclick = () => {
      action();
      parent.remove();
    };
    return item;
  }

  static createContextMenu() {
    const existing = document.querySelector(".context-menu");
    if (existing) existing.remove();

    const menu = document.createElement("div");
    menu.className = "context-menu";

    const items = {
      [lang.get("add-data")]: () => new InsertData(),
      [lang.get("rename")]: ContextMenuHandler.RenameButton,
      [lang.get("button-settings")]: () => new ButtonSettings(),
      [lang.get("customize-btn")]: () => console.log("Customize action"),
      [lang.get("delete-btn")]: ContextMenuHandler.DeleteButton,
    };

    Object.entries(items).forEach(([label, action]) => {
      const item = this.createMenuItem(label, action, menu);
      menu.appendChild(item);
    });

    document.body.appendChild(menu);
    document.addEventListener("click", this.HandleClick, { once: true });

    return menu;
  }

  static Start(event, button) {
    event.preventDefault();
    const menu = this.createContextMenu();
    menu.dataset.invoker = button.innerText;

    const mouseX = event.clientX || event.touches?.[0]?.clientX || 0;
    const mouseY = event.clientY || event.touches?.[0]?.clientY || 0;

    const menuRect = menu.getBoundingClientRect();
    const menuHeight = menuRect.height;
    const menuWidth = menuRect.width;
    const screenWidth = window.innerWidth;
    const screenHeight = window.innerHeight;

    if (screenWidth - mouseX <= menuWidth) {
      menu.style.left = screenWidth - menuWidth + "px";
      menu.style.borderRadius = "5px 0 5px 5px";

      if (screenHeight - mouseY <= menuHeight) {
        menu.style.top = mouseY - menuHeight + "px";
        menu.style.borderRadius = "5px 5px 0 5px";
      } else {
        menu.style.top = mouseY + "px";
      }
    } else {
      menu.style.left = mouseX + "px";
      menu.style.borderRadius = "0 5px 5px 5px";

      if (screenHeight - mouseY <= menuHeight) {
        menu.style.top = mouseY - menuHeight + "px";
        menu.style.borderRadius = "5px 5px 5px 0";
      } else {
        menu.style.top = mouseY + "px";
      }
    }
  }

  static HandleClick(event) {
    const menu = document.querySelector(".context-menu");
    if (menu && !menu.contains(event.target)) {
      menu.remove();
    }
  }

  static DeleteButton() {
    const menu = document.querySelector(".context-menu");
    const buttonName = menu?.dataset?.invoker;

    if (!confirm(lang.get("delete-btn-confirm") + " " + buttonName)) return;

    sendRequest({
      type: "queries",
      job: "delete button",
      button: buttonName,
    }).then(() => {
      showNotification(lang.get("delete-btn-success"));
      home.GetButtons();
    });
  }

  static RenameButton() {
    const menu = document.querySelector(".context-menu");
    const buttonName = menu?.dataset?.invoker;

    const workDiv = home.WorkDiv;
    workDiv.innerHTML = "";

    const line = lineCreator("rename-line");

    const input = Input({
      placeholder:lang.get("new-name"),
    });
    line.appendChild(Label("اسم جديد"));
    line.appendChild(input);

    const btn = Button({
      innerText: lang.get("change"),
      onclick: () => {
        const newName = input.value.trim();
        if (!newName) return showNotification(lang.get("empty"));

        sendRequest({
          type: "queries",
          job: "rename button",
          button: buttonName,
          new: newName,
        }).then((res) => {
          if (res.response === "ok") {
            showNotification(lang.get("rename-success"));
            home.GetButtons();
          }
        });
      },
    });

    line.appendChild(btn);
    workDiv.appendChild(line);
  }
}
