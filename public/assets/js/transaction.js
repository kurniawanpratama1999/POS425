const productsElement = document.getElementById("select-product");

const numberToRupiah = (val) => {
  return new Intl.NumberFormat("id-ID", {
    currency: "IDR",
    style: "currency",
    maximumFractionDigits: 0,
  }).format(val);
};

const cardProduct = (data) => {
  return `<button onclick='addProduct(${data["id"]}, "${data["name"]}", ${
    data["price"]
  })' id='btn-product-${data["id"]}' class="border p-3 flex flex-col h-fit">
  <span id='product-name'>${data["name"]}</span>
  <span id='product-price'>${numberToRupiah(data["price"])}</span>
</button>`;
};

const productsToHTML = products.map((v) => cardProduct(v));

const buttonCategories = document.querySelectorAll("button[class^=category-]");

const afterClick = (category) => {
  buttonCategories.forEach((e) => {
    e.classList.replace("bg-blue-400", "bg-white");
    e.classList.replace("text-white", "text-black");
  });

  productsElement.innerHTML = "";
  if (category.id !== "all") {
    const productsFilterToHTML = products
      .filter((vf) => vf["category_name"] == category.id)
      .map((v) => cardProduct(v));

    productsElement.insertAdjacentHTML(
      "afterbegin",
      productsFilterToHTML.join("\n")
    );
  } else {
    productsElement.insertAdjacentHTML("afterbegin", productsToHTML.join("\n"));
  }

  category.classList.replace("bg-white", "bg-blue-400");
  category.classList.replace("text-black", "text-white");
};

buttonCategories.forEach((element) => {
  element.addEventListener("click", (e) => {
    afterClick(e.target);
  });
});

productsElement.innerHTML = productsToHTML.join("\n");

let datas = [];
const addProduct = (id, name, price) => {
  const findID = datas.find((v) => v.id === id);
  if (!findID) {
    datas.push({ id, name, price, val: 1 });
    changeToHTML(datas);
  }
};

const changeToHTML = () => {
  const countingProductElement = document.getElementById("counting-product");
  countingProductElement.innerHTML = "";

  const getSubTotalElement = document.getElementById("subtotal-val");
  const getPajakElement = document.getElementById("pajak-val");
  const getTotalElement = document.getElementById("total-val");

  if (datas.length > 0) {
    const subtotal = datas.map((v) => v.price * v.val).reduce((a, b) => a + b);
    getSubTotalElement.innerHTML = numberToRupiah(subtotal);

    const pajak = subtotal * 0.1;
    getPajakElement.innerHTML = numberToRupiah(pajak);

    const total = subtotal + pajak;
    getTotalElement.innerHTML = numberToRupiah(total);
  } else {
    getSubTotalElement.innerHTML = 0;
    getPajakElement.innerHTML = 0;
    getTotalElement.innerHTML = 0;
  }

  const toHTML = datas.map(
    (v) => `
  <div>
    <span>${v["name"]}</span>
    <div class="flex flex-row gap-1">
        <button onclick="decrementProduct(${v["id"]})" class="px-4 py-1 border">-</button>
        <input type="text" value="${v["val"]}" class="text-center border px-2 py-1">
        <button onclick="incrementProduct(${v["id"]})" class="px-4 py-1 border">+</button>
        <button onclick="deleteProduct(${v["id"]})" class="px-4 py-1 border">DEL</button>
    </div> 
  </div>`
  );

  countingProductElement.innerHTML = toHTML.join("\n");
};

const decrementProduct = (id) => {
  datas = datas.map((d) => (d.id == id ? { ...d, val: d.val - 1 } : d));

  const findDatas = datas.find((v) => v.id == id);
  if (findDatas.val < 1) {
    datas = datas.filter((v) => v.id !== id);
    changeToHTML();
  }
};
const incrementProduct = (id) => {
  datas = datas.map((d) => (d.id == id ? { ...d, val: d.val + 1 } : d));
  changeToHTML();
};
const deleteProduct = (id) => {
  const findDatas = datas.find((v) => v.id == id);
  if (findDatas) {
    datas = datas.filter((v) => v.id !== id);
    changeToHTML();
  }
};

changeToHTML();
