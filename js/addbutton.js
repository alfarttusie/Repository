class AddButton {
  static start() {
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
                    params: { innerText: " الاسم : ", for: "button-name" },
                  }
                ),
                /* button name  input */
                elementCreator({
                  type: "input",
                  params: { className: "button-name", id: "button-name" },
                }),
                /** counter label */
                elementCreator({
                  type: "label",
                  params: { innerText: " عدد الجداول : " },
                }),
                /* counter input */
                elementCreator({
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
                }),
                elementCreator({
                  type: "button",
                  params: {
                    innerText: "حفظ ",
                    onclick: AddButton.SendButton,
                  },
                }),
              ],
            }
          ),
          /** Header end */
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
  static SendButton() {
    const name = document.querySelector(".button-name");
    if (name.value.length < 1) {
      ShowMsg(`اكتب اسم للزر`);
      Shake(".button-name");
      return;
    }
    const allLine = document.querySelectorAll(".button-line");
    const main = [];
    const passwords = [];
    const others = [];
    const all = [];
    allLine.forEach((line) => {
      if (line.children[2].value.length > 0) all.push(line.children[2].value);
    });
    if (allLine.length != all.length) {
      if (!confirm(`you ignored ${allLine.length - all.length} column ok?`)) {
        return;
      }
    }
    allLine.forEach((line) => {
      const column = line.children[2].value;
      const type = line.children[4].value;
      if (column.length > 0) {
        if (type == "main") main.push(column);
        else if (type == "password") passwords.push(column);
      }
    });
    sendRequest({
      type: "queries",
      job: "new button",
      button: name.value,
      main: main,
      password: passwords,
      columns: all,
    }).then((callback) => {
      console.log(callback);
      if (callback.response == "invalid key") {
        showNotification(`لا يوجد مفتاح تشفير`);
      } else if (callback.response == "Button exist") {
        showNotification(`الاسم موجود مسبقا`);
      } else if (callback.response == "successful") {
        showNotification(`تم اظافة بنجاح`);
        home.GetButtons();
      }
    });
  }
  constructor() {
    document.querySelector("#addButton").onclick = AddButton.start;
  }
}
new AddButton();
