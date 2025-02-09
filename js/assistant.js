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
  try {
    const viewBtn = document.querySelector(".view");
    if (input.style.direction == "ltr") {
      viewBtn.style.gridColumn = "70 / 74";
    } else {
      viewBtn.style.gridColumn = "26 / 31";
    }
  } catch (err) {}
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
function lineCreator(CssClass = null) {
  const line = document.createElement("div");
  line.classList.add("line");
  if (CssClass) line.classList.add(CssClass);
  return line;
}
function Button(element) {
  const Button = document.createElement("button");
  Object.entries(element).forEach(([key, value]) => {
    if (key in Button) Button[key] = value;
    else Button.setAttribute(key, value);
  });
  return Button;
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

  Object.entries(element).forEach(([key, value]) => {
    if (key in input) input[key] = value;
    else input.setAttribute(key, value);
  });
  Holder.appendChild(input);
  Holder.appendChild(viewButton);
  return Holder;
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
  const DivClass = element ? element : "WorkDiv";
  const Holder = document.querySelector(".left");
  Holder.innerHTML = "";
  const NewDiv = document.createElement("div");
  NewDiv.classList.add(DivClass);
  Holder.appendChild(NewDiv);
  return NewDiv;
}
function element(type = null, element) {
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
