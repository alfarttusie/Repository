class AddButton {
  static start() {
    console.log(`it's working..`);
    const leftDiv = document.querySelector(".left");
    leftDiv.innerHTML = "";
    elementCreator(
      /* Main */ {
        parent: leftDiv,
        params: {
          className: "addbutton-holder",
        },
        Children: [
          elementCreator(
            /* Header */ {
              params: { className: "addbutton-header" },
              Children: [
                elementCreator(
                  /*label*/ {
                    type: "label",
                    params: { innerText: " الاسم : " },
                  }
                ),
                elementCreator(/* name input */ { type: "input" }),
                elementCreator(
                  /** label */ {
                    type: "label",
                    params: { innerText: " عدد الجداول : " },
                  }
                ),
                elementCreator(
                  /* counter */ {
                    type: "input",
                    params: {
                      type: "number",
                      value: 1,
                      min: "1",
                      onwheel: (event) => {
                        if (event.deltaY < 0) {
                          event.target.value = parseInt(event.target.value) + 1;
                          AddButton.lineHandler(parseInt(event.target.value));
                        } else if (event.deltaY > 0) {
                          event.target.value =
                            parseInt(event.target.value) - 1 > 1
                              ? parseInt(event.target.value) - 1
                              : 1;
                          AddButton.lineHandler(parseInt(event.target.value));
                        }
                      },
                      onkeydown: (event) => false,
                    },
                  }
                ),
                elementCreator({
                  type: "button",
                  params: {
                    innerText: "حفظ ",
                    onclick: () => {
                      const name = document.querySelector(
                        ".addbutton-header > input"
                      );
                      if (name.value.length > 0) {
                        const allLine =
                          document.querySelectorAll(".button-line");
                        const main = [];
                        const passwords = [];
                        const others = [];
                        allLine.forEach((line) => {
                          const all = {};
                          const space = line.children[1].value;
                          const column = line.children[2].value;
                          const type = line.children[4].value;
                          if (type == "main") main.push(column);
                          else if (type == "password") passwords.push(column);
                          else others.push(column);
                          // all["space"] = space;
                          // all["column"] = column;
                          // all["type"] = type;
                          // test.push(all);
                        });
                        // sendRequest({
                        //   type: "queries",
                        //   job: "new button",
                        //   button: name,
                        // }).then((callback) => {
                        //   console.log(callback);
                        //   if (callback.response == "invalid key") {
                        //     showNotification(`لا يوجد مفتاح تشفير`);
                        //   } else if (callback.response == "Button exist") {
                        //     showNotification(`الاسم موجود مسبقا`);
                        //   } else if (callback.response == "successful") {
                        //     showNotification(`تم اظافة بنجاح`);
                        //     home.GetButtons();
                        //   }
                        // });
                      } else {
                        ShowMsg(`اكتب اسم للزر`);
                        Shake(".addbutton-header > input");
                      }
                    },
                  },
                }),
              ],
            }
          ),
          AddButton.line(),
        ],
      }
    );
  }
  static lineHandler(number) {
    const allLine = document.querySelectorAll(".button-line");
    const Holder = document.querySelector(".addbutton-holder");
    if (number > allLine.length) {
      while (true) {
        Holder.appendChild(AddButton.line());
        if (document.querySelectorAll(".button-line").length >= number) break;
      }
    } else if (allLine.length > number) {
      for (let i = allLine.length; i > number; i--) {
        allLine[i - 1].remove();
      }
    }
  }
  static line() {
    return elementCreator({
      params: { className: "line button-line" },
      Children: [
        elementCreator({ type: "label", params: { innerText: "نوع الحقل" } }),
        elementCreator({
          type: "select",
          Children: [
            elementCreator({
              type: "option",
              params: {
                value: "small",
                innerText: "حقل عادي",
              },
            }),
            elementCreator({
              type: "option",
              params: {
                value: "big",
                innerText: "حقل كبير",
              },
            }),
          ],
        }),
        elementCreator({ type: "input" }),
        elementCreator({ type: "label", params: { innerText: "مكانه الحقل" } }),
        elementCreator({
          type: "select",
          Children: [
            elementCreator({
              type: "option",
              params: {
                value: "main",
                innerText: "رئيسي",
              },
            }),
            elementCreator({
              type: "option",
              params: {
                value: "password",
                innerText: "باسورد",
              },
            }),
            elementCreator({
              type: "option",
              params: {
                value: "normal",
                innerText: "عادي",
              },
            }),
          ],
        }),
      ],
    });
  }
}

class home {
  constructor() {
    document.addEventListener("DOMContentLoaded", () => {
      console.log(localStorage.barrier);
      indicatorRemover();
      Key.checker();
      /** Event Listener */
      {
        document.querySelector("#logout").onclick = this.logOut;
        document.querySelector("#home").onclick = () =>
          (window.location = "home.php");
        document.querySelector("#addButton").onclick = AddButton.start;
        document.querySelector("#key").onclick = Key.Start;
      }
    });
  }
  logOut() {
    const userConfirmation = confirm("هل تريد تسجيل الخروج؟");
    if (userConfirmation) {
      sendRequest({ type: "log out" }).then(
        () => (window.location = "index.php")
      );
    }
  }
  static GetButtons() {
    const HolderDiv = document.querySelector(".right");
    sendRequest({ type: "queries", job: "buttons list" }).then((callback) => {
      if (callback.response == "empty") {
        HolderDiv.innerHTML = "لا توجد ازرا";
      } else if (callback.response == "ok") {
        console.log(callback.buttons);
        callback.buttons.forEach((button) =>
          elementCreator({
            parent: HolderDiv,
            type: "button",
            params: { innerText: button },
          })
        );
      }
    });
  }
}

new home();
