function indicatorRemover({ element, type } = {}) {
  const indicator = document.querySelector(".indicator");
  if (indicator) indicator.remove();
  if (element) {
    const elementHolder = document.querySelector(element);
    elementHolder.style.display = type ? type : "flex";
  }
}
function changeDirection(input) {
  const value = input.value;
  if (/^[\u0600-\u06FF]/.test(value)) {
    input.style.direction = "rtl";
    input.style.textAlign = "right";
  } else {
    input.style.direction = "ltr";
    input.style.textAlign = "left";
  }
}
async function delay(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}
function Shake(selector) {
  let element = document.querySelector(selector);
  element.classList.add("shake");
  setTimeout(() => {
    element.classList.remove("shake");
  }, 500);
}
function ShowMsg(Text) {
  const oldMsg = document.querySelector(".Msg");
  if (oldMsg) oldMsg.remove();
  const Msg = document.createElement("div");
  Msg.classList.add("Msg");
  document.body.appendChild(Msg);
  Msg.textContent = Text;
}
function responseHandler(response) {
  switch (response) {
    case "mysql off":
      ShowError(`مشكلة في قواعد البيانات`);
      break;
    case "mysql info":
      ShowError(`معلومات قاعدة البيانات غير صحيحة`);
      break;
    case "Invalid response":
      ShowError(`مشكلة في استجابة السيرفير`);
      break;
    case "database not available":
      ShowError(`قاعدة البيانات غير متوفرة`);
      break;
    case response == "Server unreachable":
      ShowError(`لا يمكن الاتصال بالسيرفير`);
      break;
    case "Bad response":
      ShowError(`مشكلة في استجابة السيرفير`);
      break;
    case "wrong requset":
      ShowError(`خطاء في الطلب`);
      break;
    case "empty":
      ShowError(`لا توجد استجابة من السيرفير`);
      break;
    default:
      ShowError(`توجد مشكلة في البرنامج`);
      break;
  }
}
function Showindicator(element) {
  let indicator = document.createElement("div");
  indicator.classList.add("indicator");
  const loading = document.createElement("p");
  indicator.appendChild(loading);
  element.appendChild(indicator);
}
function ShowError(Msg) {
  const holder = document.createElement("div");
  holder.classList.add("Showerror");
  holder.textContent = Msg;
  document.body.appendChild(holder);
}
function showNotification(Text) {
  const Holder = document.createElement("div");
  Holder.classList.add("Notify");
  const Secondary = document.createElement("div");
  Secondary.classList.add("secondary");
  const span = document.createElement("span");
  span.classList.add("Text");
  Holder.appendChild(Secondary);
  Secondary.appendChild(span);
  document.body.appendChild(Holder);
  Holder.classList.add("active");
  span.innerText = Text;
  setTimeout(() => {
    Holder.classList.remove("active");
    Holder.remove();
  }, 2500);
}
function elementCreator({
  type,
  parent = null,
  params = [],
  Children = [],
} = {}) {
  try {
    type = type ? type : "div";

    const element = document.createElement(type);

    if (params)
      Object.entries(params).forEach(([key, value]) => (element[key] = value));

    if (Children) Children.forEach((Child) => element.appendChild(Child));
    if (parent) parent.appendChild(element);

    return element;
  } catch (err) {
    console.log(err);
  }
}
function HolderDiv(CssClass = null) {
  const Holder = document.querySelector(".left");
  Holder.innerHTML = "";
  let Work = document.createElement("div");
  if (CssClass) {
    Work.classList.add(CssClass);
  } else Work.classList.add("WorkDiv");
  Holder.appendChild(Work);
  return Work;
}
function ShowPassword(event) {
  const button = event.currentTarget;
  let emoji = button.textContent == "🙉" ? "🙈" : "🙉";
  button.textContent = emoji;

  const PasswordField = button.previousElementSibling;
  let type = PasswordField.type == "text" ? "password" : "text";
  PasswordField.type = type;

  if (type == "text") {
    setTimeout(() => {
      PasswordField.type = "password";
      button.textContent = "🙈";
    }, 1500);
  }
}
function copyToClipboard(event) {
  const data =
    event.srcElement.textContent.length > 0
      ? event.srcElement.textContent
      : event.srcElement.value;
  navigator.clipboard.writeText(data);
  showNotification(`تم النسخ`);
}
function cleanWorkDiv(element = null) {
  const Holder = document.querySelector(".left");
  Holder.innerHTML = "";
  if (element) {
    const NewDiv = document.createElement("div");
    if (element) NewDiv.classList.add(element);
    Holder.appendChild(NewDiv);
    return NewDiv;
  } else {
    return Holder;
  }
}
function element_old(type = null, element) {
  type = Object.entries(element).forEach((key, value) =>
    console.log(`${key} ==> ${value}`)
  );
}
function displayEmptyMessage(Text) {
  const MainDiv = document.querySelector(".left");
  const div = cleanWorkDiv("empty-info");
  const span = document.createElement("span");
  span.innerText = Text;
  div.appendChild(span);
  MainDiv.appendChild(div);
}
function SetStyle(element, style) {
  Object.entries(style).forEach(
    ([property, value]) => (element.style[property] = value)
  );
}
function findButtonByText(text) {
  const button = Array.from(document.querySelectorAll("button")).find((btn) =>
    btn.innerHTML.includes(text)
  );
  return button;
}
/** creates elements */
{
  function Button(element) {
    const Button = document.createElement("button");
    Object.entries(element).forEach(([key, value]) => {
      if (key in Button) Button[key] = value;
      else Button.setAttribute(key, value);
    });
    return Button;
  }
  function PasswordField(element) {
    const Holder = document.createElement("p");
    Holder.classList.add("PasswordField");

    const viewButton = document.createElement("button");
    viewButton.innerText = "🙈";
    viewButton.onclick = ShowPassword;
    viewButton.addEventListener(
      "mouseenter",
      (event) => (viewButton.innerText = "🙊")
    );
    viewButton.addEventListener("mouseleave", (event) => {
      const input = viewButton.previousElementSibling;
      if (input.type == "text") viewButton.innerText = "🙉";
      else viewButton.innerText = "🙈";
    });

    const input = document.createElement("input");
    input.ondblclick = copyToClipboard;
    input.type = "password";

    Object.entries(element).forEach(([key, value]) => {
      if (key in input) input[key] = value;
      else input.setAttribute(key, value);
    });
    Holder.appendChild(input);
    Holder.appendChild(viewButton);
    return Holder;
  }
  function Input(element = null) {
    const input = document.createElement("input");
    if (element) {
      Object.entries(element).forEach(([key, value]) => {
        if (key in input) input[key] = value;
        else input.setAttribute(key, value);
      });
    }
    input.oninput = (event) => {
      changeDirection(event.target);
    };
    input.onmouseleave = () => {
      if (input.value < 1) input.style.textAlign = "center";
    };

    return input;
  }
  function Label(text, htmlFor = "") {
    const label = document.createElement("label");
    label.innerText = text;
    if (htmlFor) label.htmlFor = htmlFor;
    return label;
  }
  function lineCreator(CssClass = null) {
    const line = document.createElement("div");
    line.classList.add("line");
    if (CssClass) line.classList.add(CssClass);
    return line;
  }

  function Span(element) {
    const span = document.createElement("span");
    Object.entries(element).forEach(([key, value]) => {
      if (key in span) span[key] = value;
      else span.setAttribute(key, value);
    });
    return span;
  }
  function Element(Parameters = null) {
    try {
      const elementType = Parameters["type"] ? Parameters["type"] : "div";
      const element = document.createElement(elementType);

      if (Parameters) {
        if (Parameters["style"]) {
          Object.entries(Parameters["style"]).forEach(
            ([property, value]) => (element.style[property] = value)
          );
        }
        if (Parameters["Parrent"]) {
          Parameters["Parrent"].appendChild(element);
        }
        if (Parameters["probtype"]) {
          Object.entries(Parameters["probtype"]).forEach(([key, value]) => {
            if (key in element) element[key] = value;
            else element.setAttribute(key, value);
          });
        }
      }
      return element;
    } catch (error) {
      console.log(`Element ==> ${error}`);
      return null;
    }
  }
  function createOption(value, innerText) {
    const option = document.createElement("option");
    option.value = value;
    option.innerText = innerText;
    return option;
  }
}

/** after upgrade */

function sendRequest(data) {
  return new Promise((resolve, reject) => {
    const http = new XMLHttpRequest();
    http.open("POST", "php/requests.php", true);
    http.setRequestHeader("Content-type", "application/json");
    if (localStorage.bearer)
      http.setRequestHeader("Bearer", localStorage.bearer);
    http.onload = () => {
      try {
        localStorage.bearer = http.getResponseHeader("Bearer");
        try {
          const response = JSON.parse(http.responseText);
          resolve(response);
        } catch (error) {
          resolve("Invalid response");
        }
      } catch (error) {
        resolve("Bad response");
      }
    };
    http.onerror = () => resolve("Server is unreachable");
    http.send(JSON.stringify(data));
  });
}
