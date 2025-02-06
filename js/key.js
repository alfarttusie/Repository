class Key {
  static KeyViewer() {
    let old = document.querySelector(".encryption-key");
    if (old) return Key.hide();

    const WorkDiv = HolderDiv("encryption-key");

    const line = lineCreator("key-line");
    WorkDiv.appendChild(line);

    /** Save Button */
    line.appendChild(
      Button({
        innerText: "حفظ",
        onclick: Key.save,
        class: "key-buttons",
      })
    );

    /** value input */
    line.appendChild(
      PasswordField({
        value: "test_key",
        placeholder: "Enter encryption key",
        type: "password",
        onkeydown: (event) =>
          event.key == "Enter" ? SaveButton.click() : null,
      })
    );

    /** cancel button */
    line.appendChild(
      Button({
        innerText: "الغاء",
        class: "key-buttons",
        onclick: Key.hide,
      })
    );
  }
  static save() {
    const KeyValue = document.querySelector(".PasswordField > input");
    if (KeyValue.value.length > 0) {
      sendRequest({ type: "Set Key", key: KeyValue.value }).then((callback) => {
        if (callback.status == "successful") {
          home.GetButtons();
          showNotification(
            callback.response === "update" ? "تم التحديث" : "تم الحفظ"
          );
          document.querySelector("#key").style.backgroundColor = "transparent";
          Key.hide();
        }
      });
    } else {
      ShowMsg(`لا يمكن ترك المفتاح فارغ`);
    }
  }
  static checker() {
    sendRequest({ type: "Key checker", key: localStorage.encKey }).then(
      (response) => {
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
