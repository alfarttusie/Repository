class Key {
  static Start() {
    let old;
    try {
      old = document.querySelector(".Key-holder");
      old = old ? true : false;
    } catch (error) {
      old = false;
    }
    if (!old) {
      const Holder = document.querySelector(".left");
      Holder.innerHTML = "";
      elementCreator(
        /* Key-holder*/ {
          parent: Holder,
          params: {
            className: "Key-holder",
          },
          Children: [
            elementCreator(
              /* line */ {
                params: { className: "line key-line" },
                Children: [
                  elementCreator(
                    /* button save*/ {
                      type: "button",
                      params: {
                        innerText: "حفظ",
                        onclick: Key.save,
                      },
                    }
                  ),
                  elementCreator(
                    /*input*/ {
                      type: "input",
                      params: {
                        type: "password",
                        value: "test_key",
                        onkeydown: (event) => {
                          if (event.key === "Enter")
                            event.target.previousSibling.click();
                        },
                      },
                    }
                  ),
                  elementCreator(
                    /* button cancel */ {
                      type: "button",
                      params: {
                        innerText: "الفاء",
                        onclick: Key.hide,
                      },
                    }
                  ),
                ],
              }
            ),
          ],
        }
      );
    } else {
      Key.hide();
    }
  }
  static save() {
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
          Key.hide();
        } else {
          responseHandler(callback.status);
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
    document.querySelector("#key").onclick = Key.Start;
  }
}
new Key();
