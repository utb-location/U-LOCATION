const filters=[...document.querySelectorAll(".filter")];
const hero=document.querySelector("[data-hero-slideshow]");
const heroSlides=[...document.querySelectorAll(".hero-slide")];
const heroDots=[...document.querySelectorAll(".hero-dot")];
let heroIndex=0;
let heroTimer;
function showHero(index){if(heroSlides.length<2)return;heroIndex=(index+heroSlides.length)%heroSlides.length;heroSlides.forEach((slide,i)=>slide.classList.toggle("active",i===heroIndex));heroDots.forEach((dot,i)=>dot.classList.toggle("active",i===heroIndex));}
function stopHero(){clearInterval(heroTimer);heroTimer=undefined;}
function startHero(){if(heroSlides.length<2||window.matchMedia("(prefers-reduced-motion: reduce)").matches)return;stopHero();heroTimer=setInterval(()=>showHero(heroIndex+1),5000);}
document.querySelector(".hero-previous")?.addEventListener("click",()=>{showHero(heroIndex-1);startHero();});
document.querySelector(".hero-next")?.addEventListener("click",()=>{showHero(heroIndex+1);startHero();});
heroDots.forEach(dot=>dot.addEventListener("click",()=>{showHero(Number(dot.dataset.heroIndex));startHero();}));
startHero();
const carousel=document.querySelector("#fleetCarousel");
const previous=document.querySelector(".carousel-prev");
const next=document.querySelector(".carousel-next");
let autoScroll;
let loopTimer;
let loopCloneCount=0;
let loopJumping=false;
function originalCards(){return [...document.querySelectorAll(".coach-card:not(.carousel-clone)")];}
function visibleCards(){return originalCards().filter(card=>!card.hidden);}
function cardStep(){const card=visibleCards()[0];return card?card.getBoundingClientRect().width+16:320;}
function clearClones(){document.querySelectorAll(".carousel-clone").forEach(clone=>clone.remove());}
function jumpCarousel(left){if(!carousel)return;loopJumping=true;carousel.style.scrollBehavior="auto";carousel.scrollLeft=left;requestAnimationFrame(()=>{carousel.style.removeProperty("scroll-behavior");loopJumping=false;});}
function cloneCard(card){const clone=card.cloneNode(true);clone.classList.add("carousel-clone");clone.setAttribute("aria-hidden","true");return clone;}
function setupInfiniteLoop(){if(!carousel)return;clearClones();const cards=visibleCards();if(cards.length<2){loopCloneCount=0;carousel.scrollLeft=0;return;}const step=cardStep();loopCloneCount=Math.min(cards.length,Math.max(1,Math.round(carousel.clientWidth/step)));const before=document.createDocumentFragment(),after=document.createDocumentFragment();cards.slice(-loopCloneCount).forEach(card=>before.append(cloneCard(card)));cards.slice(0,loopCloneCount).forEach(card=>after.append(cloneCard(card)));carousel.prepend(before);carousel.append(after);jumpCarousel(step*loopCloneCount);}
function normalizeInfiniteLoop(){if(!carousel||loopJumping)return;const count=visibleCards().length;if(count<2)return;const step=cardStep(),left=carousel.scrollLeft,end=step*(loopCloneCount+count);if(left>=end-4)jumpCarousel(step*loopCloneCount);else if(left<=4)jumpCarousel(step*count);}
function scrollCarousel(direction){if(!carousel)return;carousel.scrollBy({left:direction*cardStep(),behavior:"smooth"});clearTimeout(loopTimer);loopTimer=setTimeout(normalizeInfiniteLoop,650);}
function startAutoScroll(){if(!carousel||window.matchMedia("(prefers-reduced-motion: reduce)").matches)return;clearInterval(autoScroll);autoScroll=setInterval(()=>scrollCarousel(1),3200);}
function stopAutoScroll(){clearInterval(autoScroll);}
filters.forEach(button=>button.addEventListener("click",()=>{filters.forEach(item=>item.classList.remove("active"));button.classList.add("active");clearClones();originalCards().forEach(card=>card.hidden=button.dataset.filter!=="all"&&card.dataset.category!==button.dataset.filter);setupInfiniteLoop();startAutoScroll();}));
previous?.addEventListener("click",()=>{scrollCarousel(-1);startAutoScroll();});
next?.addEventListener("click",()=>{scrollCarousel(1);startAutoScroll();});
carousel?.addEventListener("pointerdown",stopAutoScroll);
carousel?.addEventListener("pointerup",()=>{clearTimeout(loopTimer);loopTimer=setTimeout(normalizeInfiniteLoop,300);startAutoScroll();});
carousel?.addEventListener("scroll",()=>{if(loopJumping)return;clearTimeout(loopTimer);loopTimer=setTimeout(normalizeInfiniteLoop,180);},{passive:true});
carousel?.addEventListener("focusin",stopAutoScroll);
carousel?.addEventListener("focusout",startAutoScroll);
window.addEventListener("resize",()=>{clearTimeout(loopTimer);loopTimer=setTimeout(setupInfiniteLoop,180);});
document.addEventListener("visibilitychange",()=>{if(document.hidden){stopHero();stopAutoScroll();}else{startHero();startAutoScroll();}});
window.addEventListener("pageshow",()=>{startHero();startAutoScroll();});
setupInfiniteLoop();
startAutoScroll();
document.addEventListener("click",event=>{const open=event.target.closest("[data-open-vehicle]");if(open)document.getElementById(open.dataset.openVehicle).showModal();const close=event.target.closest("[data-close-dialog]");if(close)close.closest("dialog").close();const thumb=event.target.closest("[data-gallery-src]");if(thumb)thumb.closest(".detail-gallery").querySelector("[data-main-image]").src=thumb.dataset.gallerySrc;const select=event.target.closest("[data-select-vehicle]");if(select){document.getElementById("vehicleSelect").value=select.dataset.selectVehicle;select.closest("dialog").close();}});
