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
function ShowPassword() {
  let emoje = this.textContent == "🙉" ? "🙈" : "🙉";
  this.textContent = emoje;
  const PasswordField = this.previousElementSibling;
  let type = PasswordField.type == "text" ? "password" : "text";
  PasswordField.type = type;
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
          console.log(response);
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
