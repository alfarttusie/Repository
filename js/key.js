class Key {
  static KeyViewer() {
    let old = document.querySelector(".encryption-key");
    if (old) return Key.hide();

    const WorkDiv = cleanWorkDiv("encryption-key");

    const line = lineCreator("key-line");
    WorkDiv.appendChild(line);

    /** Save Button */

    line.appendChild(
      Button({
        innerText: lang.get("save-btn"),
        onclick: Key.save,
        class: "key-buttons",
      })
    );

    /** value input */
    line.appendChild(
      PasswordField({
        placeholder: "Enter encryption key",
        onkeydown: (event) => (event.key == "Enter" ? Key.save() : null),
      })
    );

    /** cancel button */
    line.appendChild(
      Button({
        innerText: lang.get("cancel-btn"),
        class: "key-buttons",
        onclick: Key.hide,
      })
    );
  }
  static save() {
    const line = document.querySelector(".PasswordField");
    const KeyValue = document.querySelector(".PasswordField > input");
    Showindicator(line);
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
    document.querySelector(".key-line").style.animationName = "close";
    setTimeout(() => {
      document.querySelector(".encryption-key").remove();
    }, 900);
  }
  constructor() {
    Key.checker();
    document.querySelector("#key").onclick = Key.KeyViewer;
  }
}
