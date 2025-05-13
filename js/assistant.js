async function delay(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}
function responseHandler({ response, element }) {
  if (element) document.querySelector(element).remove();
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
  if (element) {
    let indicator = document.createElement("div");
    indicator.classList.add("indicator");
    const loading = document.createElement("p");
    indicator.appendChild(loading);
    element.appendChild(indicator);
  } else {
    console.log(`error with Showindicator ${element}`);
  }
}
function ShowError(Msg) {
  const holder = document.createElement("div");
  holder.classList.add("Showerror");

  const textHolder = document.createElement("div");
  textHolder.classList.add("Showerror-text");
  textHolder.textContent = Msg;

  holder.appendChild(textHolder);
  document.body.appendChild(holder);

  setTimeout(() => {
    holder.classList.add("active");
  }, 900);
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
function displayEmptyMessage(Text) {
  const MainDiv = document.querySelector(".left");
  const div = cleanWorkDiv("empty-info");
  const span = document.createElement("span");
  span.innerText = Text;
  div.appendChild(span);
  MainDiv.appendChild(div);
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
    Holder.classList.add("password-field");

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
function sendRequest(data) {
  return new Promise((resolve, reject) => {
    const http = new XMLHttpRequest();
    http.open("POST", "php/requests.php", true);
    http.setRequestHeader("Content-type", "application/json");
    if (localStorage.bearer)
      http.setRequestHeader("bearer", localStorage.bearer);
    http.onload = () => {
      try {
        localStorage.bearer = http.getResponseHeader("bearer");
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
function Message(message) {
  const existingMsg = document.querySelector(".animated-message");
  if (existingMsg) {
    existingMsg.remove();
  }

  const msgDiv = document.createElement("div");
  msgDiv.className = "animated-message";
  msgDiv.innerText = message;

  msgDiv.style.position = "fixed";
  msgDiv.style.top = "-100px";
  msgDiv.style.left = "50%";
  msgDiv.style.transform = "translateX(-50%)";
  msgDiv.style.width = "0px";
  msgDiv.style.height = "40px";
  msgDiv.style.maxWidth = "500px";
  msgDiv.style.backgroundColor = "rgba(0, 0, 0, 0.85)";
  msgDiv.style.color = "#fff";
  msgDiv.style.padding = "10px 0px";
  msgDiv.style.textAlign = "center";
  msgDiv.style.borderLeft = "2px solid #fff";
  msgDiv.style.borderRight = "2px solid #fff";
  msgDiv.style.zIndex = "1000";
  msgDiv.style.boxSizing = "border-box";
  msgDiv.style.overflow = "hidden";
  msgDiv.style.opacity = "1";
  msgDiv.style.transition = "top 0.5s ease";

  document.body.appendChild(msgDiv);

  setTimeout(() => {
    msgDiv.style.top = "30%";
  }, 100);

  setTimeout(() => {
    msgDiv.style.transition = "width 0.4s ease, padding 0.4s ease";
    msgDiv.style.width = "80%";
    msgDiv.style.paddingLeft = "20px";
    msgDiv.style.paddingRight = "20px";
  }, 600);

  setTimeout(() => {
    msgDiv.style.width = "0px";
    msgDiv.style.paddingLeft = "0px";
    msgDiv.style.paddingRight = "0px";
    msgDiv.textContent = "";
  }, 2000);

  setTimeout(() => {
    msgDiv.style.transition = "top 0.5s ease, opacity 0.5s ease";
    msgDiv.style.top = "-100px";
    msgDiv.style.opacity = "0";
  }, 2600);

  setTimeout(() => {
    if (msgDiv.parentNode) {
      msgDiv.parentNode.removeChild(msgDiv);
    }
  }, 3100);
}
function elementCreator({
  type = "div",
  parent = null,
  params = {},
  Children = [],
} = {}) {
  try {
    const element = document.createElement(type);

    Object.entries(params).forEach(([key, value]) => {
      if (key in element) element[key] = value;
      else element.setAttribute(key, value);
    });

    Children.forEach((child) => element.appendChild(child));
    if (parent) parent.appendChild(element);

    return element;
  } catch (err) {
    console.error("elementCreator error:", err);
    return null;
  }
}
function showNotification(Text) {
  const Holder = document.createElement("div");
  Holder.style.direction = lang.get("lang");
  if (lang.get("lang") == "en") {
    Holder.style.left = "0px";
    Holder.style.right = "unset";
  } else {
    Holder.style.right = "0px";
    Holder.style.left = "unset";
  }
  Holder.style.flexDirection =
    lang.get("lang") === "en" ? "row-reverse" : "row";
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
function Shake(selector) {
  const element = document.querySelector(selector);
  if (!element) return;
  element.classList.add("shake");
  setTimeout(() => {
    element.classList.remove("shake");
  }, 500);
}
function startCountdown(seconds, element = null) {
  const old = document.querySelector(".countdown-timer");
  if (old) old.remove();

  const countdownElement = document.createElement("div");
  countdownElement.className = "countdown-timer";
  countdownElement.style.fontSize = "24px";
  countdownElement.style.fontWeight = "bold";
  if (element) {
    element.appendChild(countdownElement);
  } else {
    document.body.appendChild(countdownElement);
  }

  function updateCountdown() {
    if (seconds <= 0) {
      countdownElement.remove();
      clearInterval(timer);
      return;
    }

    let mins = Math.floor(seconds / 60);
    let secs = seconds % 60;

    let display =
      mins > 0
        ? `وقت الحضر ${mins} دقيقة و ${secs < 10 ? "0" : ""}${secs} ثانية`
        : `${secs < 10 ? "0" : ""}${secs} ثانية`;

    countdownElement.textContent = display;
    seconds--;
  }

  updateCountdown();
  const timer = setInterval(updateCountdown, 1000);
}
function Clicker(event, Button) {
  if (event.key === "Enter") {
    Button.click();
  }
}
function getElementByText(text) {
  const xpath = `//*[normalize-space(text()) = "${text}"]`;
  const result = document.evaluate(
    xpath,
    document,
    null,
    XPathResult.FIRST_ORDERED_NODE_TYPE,
    null
  );
  return result.singleNodeValue;
}
