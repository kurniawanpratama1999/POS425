const productsElement = document.getElementById("select-product");

const numberToRupiah = (val) => {
  return new Intl.NumberFormat("id-ID", {
    currency: "IDR",
    style: "currency",
    maximumFractionDigits: 0,
  }).format(val);
};

const cardProduct = (data) => {
  const id = data["id"];
  const name = '"' + data["name"] + '"';
  const category_id = data["category_id"];
  const category_name = '"' + data["category_name"] + '"';
  const price = data["price"];

  const sendData = [id, name, category_id, category_name, price];

  return `<button onclick='addProduct(${sendData})' id='btn-product-${id}' class="border p-3 flex flex-col h-fit">
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
      .filter((vf) => vf["category_id"] == category.id)
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
const addProduct = (id, name, category_id, category_name, price) => {
  const findID = datas.find((v) => v.id === id);
  if (!findID) {
    datas.push({ id, name, category_id, category_name, price, val: 1 });
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

    const pajak = subtotal * 0.11;
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
  <div class='grid grid-cols-[1fr_10rem_150px] gap-3 italic font-mono mt-2'>
    <span>${v["name"]}</span>
    <div class="grid grid-cols-4 text-xs w-40 place-content-center place-items-center">
      <button onclick="decrementProduct(${
        v["id"]
      })" class="block outline outline-white w-5 h-5 rounded-full bg-red-400 font-bold text-white">-</button>
      <p class="text-center outline-0 border-0 w-10">${v["val"]}</p>
      <button onclick="incrementProduct(${
        v["id"]
      })" class="block outline outline-white w-5 h-5 rounded-full bg-green-400 font-bold text-white">+</button>
      <button onclick="deleteProduct(${
        v["id"]
      })" class="block outline outline-white w-5 h-5 rounded-full bg-red-400 font-bold text-white">o</button>
    </div> 
    <span class='text-right'>${numberToRupiah(v["price"])}</span>
  </div>`
  );

  countingProductElement.innerHTML = toHTML.join("\n");
};

const decrementProduct = (id) => {
  datas = datas.map((d) => (d.id == id ? { ...d, val: d.val - 1 } : d));

  const findDatas = datas.find((v) => v.id == id);
  if (findDatas.val < 1) {
    datas = datas.filter((v) => v.id !== id);
  }
  changeToHTML();
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

const elementClearTransaction = document.getElementById("clear-transaction");
const elementPaymentTransaction = document.getElementById(
  "payment-transaction"
);

elementClearTransaction.addEventListener("click", () => {
  datas = [];
  changeToHTML();
});

elementPaymentTransaction.addEventListener("click", async () => {
  try {
    const date = new Date();

    const pad = (num) => String(num).padStart(2, "0");

    const dateformatted =
      pad(date.getDate()) +
      pad(date.getMonth() + 1) +
      date.getFullYear() +
      pad(date.getHours()) +
      pad(date.getMinutes()) +
      pad(date.getSeconds());

    const subtotal = datas.map((v) => v.price * v.val).reduce((a, b) => a + b);
    const tax_rate = 0.11;
    const total_amount = subtotal + subtotal * tax_rate;

    const code = `ORD${dateformatted}`;

    const response = await fetch("/dashboard/transactions/store", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({
        items: datas, // kirim sebagai objek
        subtotal,
        tax_rate,
        total_amount,
        code,
      }),
    });

    const result = await response.json();
    if (!response.ok) {
      throw new Error(result.message || "Gagal menyimpan transaksi");
    }

    alert("Transaksi berhasil!");
    console.log(result);
    // window.location.href = "/dashboard/transactions/print";
  } catch (error) {
    alert("Terjadi kesalahan: " + error.message);
  }
});
