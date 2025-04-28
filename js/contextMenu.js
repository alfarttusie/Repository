class ContextMenuHandler {
  static timeout;
  static lastTap = 0;

  // إنشاء قائمة السياق
  static createContextMenu() {
    // إزالة أي قائمة سياق موجودة
    const existingMenu = document.querySelector(".context-menu");
    if (existingMenu) {
      existingMenu.remove();
    }

    const menu = document.createElement("div");
    menu.classList.add("context-menu");

    const items = {
      "إضافة بيانات": () => new InsertData(),
      "إعادة تسمية": ContextMenuHandler.RenameButton,
      "اعدادت الزر": () => new ButtonSettings(),
      تخصيص: () => console.log("Customize action"),
      "حذف الزر": ContextMenuHandler.DeleteButton,
    };

    // إنشاء عناصر القائمة وإضافتها
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
    const buttonName = menu?.dataset?.invoker || "غير معروف";

    sendRequest({
      type: "queries",
      job: "delete button",
      button: buttonName,
    }).then((callback) => {
      showNotification("تم حذف الزر بنجاح!");
      home.GetButtons();
    });
  }

  static RenameButton() {
    const menu = document.querySelector(".context-menu");
    const buttonName = menu?.dataset?.invoker || "غير معروف";

    const workDiv = home.WorkDiv;
    const line = document.createElement("div");
    line.onkeydown = (event) => {
      if (event.key == "Enter") {
        renameButton.click();
      }
    };
    line.classList.add("line");

    const inputField = document.createElement("input");
    inputField.placeholder = "أدخل الاسم الجديد";

    line.appendChild(inputField);

    const renameButton = document.createElement("button");
    renameButton.innerText = "تغيير";
    renameButton.onclick = () => {
      const newName = inputField.value.trim();
      if (!newName) {
        showNotification("يرجى إدخال اسم جديد صالح.");
        return;
      }

      sendRequest({
        type: "queries",
        job: "rename button",
        button: buttonName,
        new: newName,
      }).then((callback) => {
        if (callback.response == "ok") {
          showNotification("تم تغيير الاسم بنجاح!");
          home.GetButtons();
        }
      });
    };

    line.appendChild(renameButton);
    workDiv.appendChild(line);
  }
}
