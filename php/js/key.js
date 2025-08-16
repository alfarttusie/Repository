class Key {
  static KeyViewer() {
    let old = document.querySelector(".encryption-key");
    if (old) return Key.hide();
    elementCreator({
      parent: document.body,
      type: "div",
      Children: [
        Button({
          innerText: lang.get("cancel-btn"),
          class: "key-buttons",
          onclick: Key.hide,
        }),
        PasswordField({
          placeholder: "Enter encryption key",
          onkeydown: (event) => (event.key == "Enter" ? Key.save() : null),
        }),
        Button({
          innerText: lang.get("save-btn"),
          onclick: Key.save,
          class: "key-buttons",
        }),
      ],
      params: {
        classList: "encryption-key",
        style: { animationName: "open" },
      },
    });
  }
  static save() {
    const Holder = document.querySelector(".encryption-key ");
    const KeyValue = Holder.querySelector(".password-field > input");
    Showindicator(Holder);
    if (KeyValue.value.length > 0) {
      sendRequest({ type: "Set Key", key: KeyValue.value }).then((callback) => {
        indicatorRemover();
        if (callback.status == "successful") {
          home.GetButtons();
          showNotification(
            callback.response === "update"
              ? lang.get("notification-update")
              : lang.get("notification-save")
          );
          document.querySelector("#key").style.backgroundColor = "transparent";
          Key.hide();
        }
      });
    } else {
      indicatorRemover();
      showNotification(lang.get("empty-key"));
    }
  }
  static checker() {
    const KeyButton = document.querySelector("#key");
    Showindicator(KeyButton);
    sendRequest({ type: "Key checker", key: localStorage.encKey }).then(
      (response) => {
        indicatorRemover();
        if (response.status != "successful") {
          Key.KeyViewer();
          document.querySelector("#key").style.backgroundColor = "red";
        } else if (response.status == "successful") {
          home.GetButtons();
        }
      }
    );
  }
  static hide() {
    document.querySelector(".encryption-key").style.animationName = "close";
    setTimeout(() => {
      document.querySelector(".encryption-key").remove();
    }, 900);
  }
  constructor() {
    Key.checker();
    document.querySelector("#key").onclick = Key.KeyViewer;
  }
}
