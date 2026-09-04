document.addEventListener("DOMContentLoaded",()=>{
  initBanner();
  initCartForms();
});

function initBanner(){
  const b=document.querySelector(".msh-banner");
  if(!b)return;
  const s=b.querySelectorAll(".msh-banner-slide"),d=document.querySelectorAll(".msh-dot");
  if(s.length<=1)return;
  let c=0,t;
  const go=i=>{s[c]?.classList.remove("active");d[c]?.classList.remove("active");c=(i+s.length)%s.length;s[c]?.classList.add("active");d[c]?.classList.add("active")};
  d.forEach((x,i)=>x.addEventListener("click",()=>{go(i);reset()}));
  const reset=()=>{clearInterval(t);t=setInterval(()=>go(c+1),4500)};
  reset();
}

function showToast(message,type="success"){
  const root=document.getElementById("msh-toast-root");
  if(!root)return;
  const toast=document.createElement("div");
  toast.className=`msh-toast msh-toast--${type}`;
  toast.textContent=message;
  root.appendChild(toast);
  requestAnimationFrame(()=>toast.classList.add("show"));
  setTimeout(()=>{
    toast.classList.remove("show");
    setTimeout(()=>toast.remove(),300);
  },2600);
}

function updateCartBadges(count){
  ["msh-cart-count-header","msh-cart-count-nav"].forEach(id=>{
    const el=document.getElementById(id);
    if(!el){
      if(count<=0)return;
      const cartLink=document.querySelector(id==="msh-cart-count-header"?'.msh-header-actions a[href*="cart"]':'.msh-nav-item[href*="cart"]');
      if(!cartLink)return;
      const badge=document.createElement("span");
      badge.className="msh-badge-dot";
      badge.id=id;
      badge.textContent=count;
      cartLink.appendChild(badge);
      return;
    }
    if(count>0){
      el.textContent=count;
      el.style.display="";
    }else{
      el.remove();
    }
  });
}

function initCartForms(){
  document.querySelectorAll(".msh-cart-add-form").forEach(form=>{
    form.addEventListener("submit",async e=>{
      e.preventDefault();
      const btn=form.querySelector('button[type="submit"]');
      if(btn)btn.disabled=true;
      try{
        const res=await fetch(form.action,{
          method:"POST",
          headers:{
            "X-Requested-With":"XMLHttpRequest",
            "Accept":"application/json",
            "X-CSRF-TOKEN":form.querySelector('input[name="_token"]')?.value||""
          },
          body:new FormData(form)
        });
        const data=await res.json().catch(()=>({}));
        if(!res.ok){
          if(res.status===422&&data.errors){
            const first=Object.values(data.errors).flat()[0];
            throw new Error(first||data.message||"Could not add to cart.");
          }
          throw new Error(data.message||"Could not add to cart.");
        }
        showToast(data.message||"Added to cart.");
        if(typeof data.cart_count==="number")updateCartBadges(data.cart_count);
      }catch(err){
        showToast(err.message||"Could not add to cart.","error");
      }finally{
        if(btn){
          const variantInput=document.getElementById("variant-id-input");
          btn.disabled=variantInput ? !variantInput.value : false;
        }
      }
    });
  });
}
