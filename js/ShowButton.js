class ShowButton {
  constructor(buttonName) {
    this.loadData(buttonName);
  }

  async loadData(buttonName) {
    try {
      const callback = await sendRequest({
        type: "queries",
        job: "show Button",
        button: buttonName,
      });

      if (callback.response === "no data")
        return this.displayEmptyMessage("لا توجد معلومات");
      if (callback.response === "no columns")
        return this.displayEmptyMessage("لا توجد أعمدة في هذا الزر");

      const holderDiv = home.WorkDiv;
      holderDiv.innerHTML = "";

      callback.data.forEach((data) => {
        const line = this.createElementWithClass("div", "line");

        const mainContent = this.createMainContent(data.main || "empty");
        line.appendChild(mainContent);

        const passwordContent = this.createPasswordFields(
          data.passwords || "empty"
        );
        line.appendChild(passwordContent);

        const detailsButton = this.createElementWithClass("button");
        detailsButton.innerText = "تفاصيل";
        detailsButton.onclick = () => this.showId(data.id, buttonName);
        line.appendChild(detailsButton);

        holderDiv.appendChild(line);
      });
    } catch (error) {
      console.error("Error loading button data:", error);
      this.displayEmptyMessage("حدث خطأ أثناء تحميل البيانات");
    }
  }

  createMainContent(mains) {
    if (mains === "empty") {
      return this.createMessageElement("لا يتواجد أعمدة");
    }

    const mainHolder = this.createElementWithClass("div", "main-holder");

    Object.entries(mains).forEach(([key, value]) => {
      const wrapper = this.createElementWithClass("div", "main-item");
      const label = this.createElementWithClass("label");
      label.innerText = `${key} : `;
      const mainValue = this.createElementWithClass("span");
      mainValue.innerText = value;

      wrapper.appendChild(label);
      wrapper.appendChild(mainValue);
      mainHolder.appendChild(wrapper);
    });

    return mainHolder;
  }

  createPasswordFields(passwords) {
    if (passwords === "empty") {
      return this.createMessageElement("لا يتوجد أعمدة");
    }

    const passwordHolder = this.createElementWithClass(
      "div",
      "passwords-holder"
    );

    Object.entries(passwords).forEach(([key, value]) => {
      const wrapper = this.createElementWithClass("div", "password-item");
      const label = this.createElementWithClass("label");
      label.innerText = `${key} : `;
      const passwordValue = this.createElementWithClass("input");
      passwordValue.type = "password";
      passwordValue.value = value;

      wrapper.appendChild(label);
      wrapper.appendChild(passwordValue);
      passwordHolder.appendChild(wrapper);
    });

    return passwordHolder;
  }

  displayEmptyMessage(message) {
    const holderDiv = home.WorkDiv;
    holderDiv.innerHTML = "";

    const msg = this.createElementWithClass("div", "empty-info");
    const span = this.createElementWithClass("span");
    span.innerText = message;

    msg.appendChild(span);
    holderDiv.appendChild(msg);
  }

  async showId(id, buttonName) {
    try {
      const callback = await sendRequest({
        type: "queries",
        job: "select id",
        button: buttonName,
        id: id,
      });

      const holderDiv = home.WorkDiv;
      holderDiv.innerHTML = "";

      Object.entries(callback.data).forEach(([key, value]) => {
        if (key === "id") return;

        const line = this.createElementWithClass("div", "line");
        const label = this.createElementWithClass("label");
        label.innerText = `${key} : `;
        const valueSpan = this.createElementWithClass("span");
        valueSpan.innerText = value;

        line.appendChild(label);
        line.appendChild(valueSpan);
        holderDiv.appendChild(line);
      });
    } catch (error) {
      console.error("Error fetching details:", error);
      this.displayEmptyMessage("حدث خطأ أثناء تحميل التفاصيل");
    }
  }

  createMessageElement(message) {
    const messageDiv = this.createElementWithClass("div", "empty-message");
    messageDiv.innerText = message;
    return messageDiv;
  }

  createElementWithClass(tag, className = "") {
    const element = document.createElement(tag);
    if (className) element.classList.add(className);
    return element;
  }
}
