/* BTN HANDLER */
const selectedCategory = {value:"all"};
const searchingProduct = {value:''};
const datas = {value:products};
const allDatas = [...products];
const insertProduct = {value:[]};

const handleCategoriesSelected = () => {
    const elementSelectCategory = document.querySelector("select[id=category]")
    selectedCategory.value = elementSelectCategory.value
}

const debounceFilter = {value: null};
const handleFilteringProducts = () => {
    const elementSearching = document.querySelector("input[name=search]")
    searchingProduct.value = elementSearching.value.trim().toLowerCase();
    
    if (debounceFilter.value) {
        clearTimeout(debounceFilter.value)
    }

    debounceFilter.value = setTimeout(() => {
        datas.value = [...allDatas];
        datas.value = datas.value.filter(data => Object.values(data).some((val) => String(val).toLowerCase().includes(searchingProduct.value)))
        renderTableProducts()
    }, 500)
}

const incrementQty = () => {
    const elementQty = document.querySelector('input[name=qty]')
    elementQty.value = Number(elementQty.value) + 1
}

const decrementQty = () => {
    const elementQty = document.querySelector('input[name=qty]')
    if (elementQty.value > 1) {
        elementQty.value = Number(elementQty.value) - 1
    }
}

const cancelInsertProduct = () => {
    document.getElementById('modal-modify-insert-product').remove()
}

const OkInsertProduct = (data) => {
    const elementQty = document.querySelector('input[name=qty]')

    const id = Number(data.id);
    const qty = Number(elementQty.value);
    const price = Number(data.price);
    const subtotal = price * qty;
    const taxPrecentage = 0.11;
    const tax = subtotal * taxPrecentage;
    const total = subtotal + tax;

    const findProductWhenInsert = insertProduct.value.find(p => p.id == id);

    if (findProductWhenInsert) {
        insertProduct.value = insertProduct.value.map(p => p.id == id ? ({...p, qty: qty, subtotal: subtotal, tax: tax, total: total}) : p)
    } else {
        insertProduct.value = [...insertProduct.value, {...data, id: id, price: price, qty, subtotal, tax, total}]
    }
    
    document.getElementById('modal-modify-insert-product').remove()

    renderProductSelected();
}



/* ANOTHER FUNCTION */
const numberToRupiah = (n) => {
    return new Intl.NumberFormat("id-ID", {currency: "IDR", style: "currency", maximumFractionDigits: 0}).format(n)
}

/* MODAL POPUP */
const modalModifyInsertProduct =  (data, isDelete = false) => {
    const elementBody = document.body

    const sendToOkInsertProduct = JSON.stringify(data);

    const elementForm = `
    <div id='modal-modify-insert-product' class='fixed top-0 left-0 w-full h-full bg-slate-100/10 backdrop-blur-md flex items-center justify-center'>
        <div class='p-3 rounded-md shadow bg-white grid gap-3'>
            <div class='grid grid-cols-[auto_1fr] gap-5'>
                <div class='flex flex-col gap-2'>
                    <p>id</p>
                    <p>name</p>
                    <p>category</p>
                    <p>price</p>
                </div>
                <div class='flex flex-col gap-2'>
                    <p> : ${data.id}</p>
                    <p> : ${data.name}</p>
                    <p> : ${data.category_name}</p>
                    <p> : ${numberToRupiah(data.price)}</p>
                </div>
            </div>

            <label for='qty' class='p-1! flex flex-row gap-2 bg-slate-100'>
                <button onclick='decrementQty()' class='px-3 py-1 bg-red-300 font-bold text-white'>-</button>
                <input 
                    class='text-center border-0 outline-0'
                    type='number' 
                    min='1' step='1' 
                    name='qty' value='1' 
                    autocomplete='off' autocorrect='off'>
                <button type='button' onclick='incrementQty()' class='px-3 py-1 bg-emerald-300 font-bold text-white'>+</button>
            </label>
                
            <div class='grid ${isDelete ? "grid-cols-3" : "grid-cols-2"} gap-3'>
                ${isDelete ? "<button type='button' onclick='deleteInsertProduct()' class='px-3 py-2 bg-neutral-400 font-bold text-white'>DELETE</button>" : ""}
                <button type='button' onclick='cancelInsertProduct()' class='px-3 py-2 bg-red-300 font-bold text-white'>CANCEL</button>
                <button type='button' id='ok-insert-product' onclick='OkInsertProduct(${sendToOkInsertProduct})' class='px-3 py-2 bg-emerald-300 font-bold text-white'>INSERT</button>
            </div>
        </div>
    </div>
    `
    elementBody.insertAdjacentHTML('afterbegin', elementForm)
} 

/* CREATE DYNAMICS HTML */
const htmlTableProducts = (data) => {
    const id = data.id;
    const category_name = data.category_name;
    const name = data.name;
    const price = data.price;
    const stock = data.stock;

    const sendData = JSON.stringify({id, name, category_name, price, stock});

    return `<tr onclick='modalModifyInsertProduct(${sendData})'>
                <td>${id}</td>
                <td>${name}</td>
                <td>${price}</td>
                <td>${stock}</td>
            </tr>`
}

const htmlProductSelected = (data) => {
    const sendData = JSON.stringify(data);

    return `
    <tr onclick='modalModifyInsertProduct(${sendData}, true)'>
        <td class='align-top'>${data.name}</td>
        <td class='align-top'>x${data.qty}</td>
        <td class='align-top'>${numberToRupiah(data.price)}</td>
        <td>
            <div class='flex! flex-col! gap-0! text-right items-end!'>
            <p class='font-bold'>${numberToRupiah(data.total)}</p>
            <p class='text-xs italic font-mono text-nowrap'>(tax) ${numberToRupiah(data.tax)}</p>
            </div>
        </td>
    </tr>
    `
}

/* RENDERING */
const renderTableProducts = () => {
    const elementProductList = document.getElementById("product-list");
    const mappingProduct = datas.value.length !== 0 
    ? datas.value.map((product) => htmlTableProducts(product)).join("\n")
    : `<tr><td colspan='100%' class='text-center'>Kosong loh ya</td></tr>`

    elementProductList.innerHTML = mappingProduct;
}
const renderProductSelected = () => {
    const elementProductSelected = document.getElementById('product-selected');
    const mappingProduct = datas.value.length !== 0 
    ? insertProduct.value.map((p) => htmlProductSelected(p)).join("\n")
    : `<tr><td colspan='100%' class='text-center'>Jangan lupa senyum, sapa, salam</td></tr>`

    elementProductSelected.innerHTML = mappingProduct;
}

const rendering = () => {
    renderTableProducts()
}

rendering();

/* KEYBOARD HANDLER */
document.addEventListener("keydown", (event) => {
  const modal = document.getElementById("modal-modify-insert-product");
  const elementSearch = document.querySelector("input[name=search]");
  const rows = document.querySelectorAll("#product-list tr");

  // 1️⃣ ESC → Tutup modal jika sedang terbuka
  if (event.key === "Escape" && modal) {
    cancelInsertProduct();
  }

  else if (event.key === "ArrowRight" && modal) {
    incrementQty();
  }

  else if (event.key === "ArrowLeft" && modal) {
    decrementQty();
  }

  // 2️⃣ ENTER → Jika modal terbuka → klik tombol INSERT
  else if (event.key === "Enter" && modal) {
    const insertButton = modal.querySelector("#ok-insert-product");
    if (insertButton) insertButton.click();
  }

  // 3️⃣ ENTER → Jika sedang fokus di input search → buka produk pertama
  else if (event.key === "Enter" && document.activeElement === elementSearch) {
    if (rows.length > 0) {
      rows[0].click(); // buka produk pertama
    }
  }

  // 4️⃣ Panah Atas/Bawah → navigasi di tabel produk
  else if ((event.key === "ArrowUp" || event.key === "ArrowDown") && rows.length > 0) {
    event.preventDefault();

    // cari elemen yang sedang dipilih
    let currentIndex = Array.from(rows).findIndex((r) =>
      r.classList.contains("bg-emerald-100")
    );

    // hilangkan highlight lama
    rows.forEach((r) => r.classList.remove("bg-emerald-100"));

    if (event.key === "ArrowDown") {
      currentIndex = (currentIndex + 1) % rows.length;
    } else if (event.key === "ArrowUp") {
      currentIndex = (currentIndex - 1 + rows.length) % rows.length;
    }

    const selectedRow = rows[currentIndex];
    selectedRow.classList.add("bg-emerald-100");
    selectedRow.scrollIntoView({ behavior: "smooth", block: "nearest" });
  }

  // 5️⃣ ENTER → jika baris sedang di-highlight, buka modal produk
  else if (event.key === "Enter" && rows.length > 0) {
    const selected = document.querySelector("#product-list tr.bg-emerald-100");
    if (selected) selected.click();
  }
});
