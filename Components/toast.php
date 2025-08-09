<div id="toast_box"></div>
<script>
let toast_box = document.getElementById("toast_box");
let success_icon = "<i class='fas fa-check-circle'></i>";
let error_icon = "<i class='fas fa-xmark-circle'></i>";
let invalid_icon = "<i class='fas fa-exclamation-circle'></i>";

function show_toast(msg, type) {
    msg = msg.charAt(0).toUpperCase() + msg.slice(1);
    let toast = document.createElement("div");

    switch(type) {
        case "success":
            toast.innerHTML = success_icon + msg;
            break;
        case "error":
            toast.innerHTML = error_icon + msg;
            break;
        case "invalid":
            toast.innerHTML = invalid_icon + msg;
            break;
        default:
            toast.innerHTML = msg;
    }

    toast.classList.add("toast");
    toast.classList.add(type);

    toast_box.appendChild(toast);

    setTimeout(()=>{
        toast.remove();
    }, 5000);
}</script>