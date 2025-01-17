class Key {
  static KeyViewer() {
    let old = document.querySelector(".Key-holder");
    if (old) return Key.hide();

    const WorkDiv = home.WorkDiv;

    const Holder = document.createElement("div");
    Holder.classList.add("Key-holder");

    const line = document.createElement("div");
    line.classList.add("line");
    line.classList.add("key-line");
    Holder.appendChild(line);

    const SaveButton = document.createElement("button");
    SaveButton.onclick = Key.save;
    SaveButton.innerText = "حفظ";
    line.appendChild(SaveButton);

    const KeyValue = document.createElement("input");
    KeyValue.type = "password";
    KeyValue.value = "test_key";
    KeyValue.onkeydown = (event) =>
      event.key == "Enter" ? SaveButton.click() : null;
    line.appendChild(KeyValue);

    const cancelButton = document.createElement("button");
    cancelButton.innerText = "الفاء";
    cancelButton.onclick = Key.hide;
    line.appendChild(cancelButton);

    WorkDiv.appendChild(Holder);

    return WorkDiv;
  }
  static save() {
    const KeyValue = document.querySelector(".key-line > input");
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
      document.querySelector(".Key-holder").remove();
    }, 900);
  }
  constructor() {
    Key.checker();
    document.querySelector("#key").onclick = Key.KeyViewer;
  }
}
