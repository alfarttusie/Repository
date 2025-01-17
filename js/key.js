class Key {
  KeyViewer() {
    const WorkDiv = home.WorkDiv;
    let old = document.querySelector(".Key-holder");
    if (old) {
      this.hide();
      return;
    }
    const Holder = document.createElement("div");
    Holder.classList.add("Key-holder");

    const line = document.createElement("div");
    line.classList.add("line");
    line.classList.add("key-line");
    Holder.appendChild(line);

    const SaveButton = document.createElement("button");
    SaveButton.onclick = this.save;
    SaveButton.innerText = "حفظ";
    line.appendChild(SaveButton);

    const KeyValue = document.createElement("input");
    KeyValue.type = "password";
    KeyValue.value = "test_key";
    line.appendChild(KeyValue);

    const cancelButton = document.createElement("button");
    cancelButton.innerText = "الفاء";
    cancelButton.onclick = this.hide;
    line.appendChild(cancelButton);

    WorkDiv.appendChild(Holder);

    return WorkDiv;
  }

  save() {
    const KeyValue = document.querySelector(".key-line > input");
    if (KeyValue.value.length > 0) {
      sendRequest({ type: "Set Key", key: KeyValue.value }).then((callback) => {
        if (callback.status == "successful") {
          home.GetButtons();
          localStorage.encKey = callback.key;
          showNotification(
            callback.response === "update" ? "تم التحديث" : "تم الحفظ"
          );
          document.querySelector("#key").style.backgroundColor = "transparent";
          this.hide();
        } else {
          responseHandler(callback.status);
        }
      });
    } else {
      ShowMsg(`لا يمكن ترك المفتاح فارغ`);
    }
  }
  checker() {
    alert(`from check`);
    sendRequest({ type: "Key checker", key: localStorage.encKey }).then(
      (response) => {
        if (response.status != "successful") {
          document.querySelector("#key").style.backgroundColor = "red";
        } else if (response.status == "successful") {
          home.GetButtons();
        }
      }
    );
  }
  hide() {
    document.querySelector(".key-line").style.animationName = "close";
    setTimeout(() => {
      document.querySelector(".Key-holder").remove();
    }, 900);
  }
  constructor() {
    this.checker();
    document.querySelector("#key").onclick = this.KeyViewer;
  }
}
