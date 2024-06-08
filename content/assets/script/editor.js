/**
 * Copyright (c) 2022-2024 UFOCMS
 *
 * This software is licensed under the GPLv3 license.
 * See the LICENSE file for more information.
 */

/**
 * Color picker | https://github.com/simonwep/pickr
 */
!function(t,e){"object"==typeof exports&&"object"==typeof module?module.exports=e():"function"==typeof define&&define.amd?define([],e):"object"==typeof exports?exports.Pickr=e():t.Pickr=e()}(self,(function(){return(()=>{"use strict";var t={d:(e,o)=>{for(var n in o)t.o(o,n)&&!t.o(e,n)&&Object.defineProperty(e,n,{enumerable:!0,get:o[n]})},o:(t,e)=>Object.prototype.hasOwnProperty.call(t,e),r:t=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})}},e={};t.d(e,{default:()=>L});var o={};function n(t,e,o,n,i={}){e instanceof HTMLCollection||e instanceof NodeList?e=Array.from(e):Array.isArray(e)||(e=[e]),Array.isArray(o)||(o=[o]);for(const s of e)for(const e of o)s[t](e,n,{capture:!1,...i});return Array.prototype.slice.call(arguments,1)}t.r(o),t.d(o,{adjustableInputNumbers:()=>p,createElementFromString:()=>r,createFromTemplate:()=>a,eventPath:()=>l,off:()=>s,on:()=>i,resolveElement:()=>c});const i=n.bind(null,"addEventListener"),s=n.bind(null,"removeEventListener");function r(t){const e=document.createElement("div");return e.innerHTML=t.trim(),e.firstElementChild}function a(t){const e=(t,e)=>{const o=t.getAttribute(e);return t.removeAttribute(e),o},o=(t,n={})=>{const i=e(t,":obj"),s=e(t,":ref"),r=i?n[i]={}:n;s&&(n[s]=t);for(const n of Array.from(t.children)){const t=e(n,":arr"),i=o(n,t?{}:r);t&&(r[t]||(r[t]=[])).push(Object.keys(i).length?i:n)}return n};return o(r(t))}function l(t){let e=t.path||t.composedPath&&t.composedPath();if(e)return e;let o=t.target.parentElement;for(e=[t.target,o];o=o.parentElement;)e.push(o);return e.push(document,window),e}function c(t){return t instanceof Element?t:"string"==typeof t?t.split(/>>/g).reduce(((t,e,o,n)=>(t=t.querySelector(e),o<n.length-1?t.shadowRoot:t)),document):null}function p(t,e=(t=>t)){function o(o){const n=[.001,.01,.1][Number(o.shiftKey||2*o.ctrlKey)]*(o.deltaY<0?1:-1);let i=0,s=t.selectionStart;t.value=t.value.replace(/[\d.]+/g,((t,o)=>o<=s&&o+t.length>=s?(s=o,e(Number(t),n,i)):(i++,t))),t.focus(),t.setSelectionRange(s,s),o.preventDefault(),t.dispatchEvent(new Event("input"))}i(t,"focus",(()=>i(window,"wheel",o,{passive:!1}))),i(t,"blur",(()=>s(window,"wheel",o)))}const{min:u,max:h,floor:d,round:m}=Math;function f(t,e,o){e/=100,o/=100;const n=d(t=t/360*6),i=t-n,s=o*(1-e),r=o*(1-i*e),a=o*(1-(1-i)*e),l=n%6;return[255*[o,r,s,s,a,o][l],255*[a,o,o,r,s,s][l],255*[s,s,a,o,o,r][l]]}function v(t,e,o){const n=(2-(e/=100))*(o/=100)/2;return 0!==n&&(e=1===n?0:n<.5?e*o/(2*n):e*o/(2-2*n)),[t,100*e,100*n]}function b(t,e,o){const n=u(t/=255,e/=255,o/=255),i=h(t,e,o),s=i-n;let r,a;if(0===s)r=a=0;else{a=s/i;const n=((i-t)/6+s/2)/s,l=((i-e)/6+s/2)/s,c=((i-o)/6+s/2)/s;t===i?r=c-l:e===i?r=1/3+n-c:o===i&&(r=2/3+l-n),r<0?r+=1:r>1&&(r-=1)}return[360*r,100*a,100*i]}function y(t,e,o,n){e/=100,o/=100;return[...b(255*(1-u(1,(t/=100)*(1-(n/=100))+n)),255*(1-u(1,e*(1-n)+n)),255*(1-u(1,o*(1-n)+n)))]}function g(t,e,o){e/=100;const n=2*(e*=(o/=100)<.5?o:1-o)/(o+e)*100,i=100*(o+e);return[t,isNaN(n)?0:n,i]}function _(t){return b(...t.match(/.{2}/g).map((t=>parseInt(t,16))))}function w(t){t=t.match(/^[a-zA-Z]+$/)?function(t){if("black"===t.toLowerCase())return"#000";const e=document.createElement("canvas").getContext("2d");return e.fillStyle=t,"#000"===e.fillStyle?null:e.fillStyle}(t):t;const e={cmyk:/^cmyk[\D]+([\d.]+)[\D]+([\d.]+)[\D]+([\d.]+)[\D]+([\d.]+)/i,rgba:/^((rgba)|rgb)[\D]+([\d.]+)[\D]+([\d.]+)[\D]+([\d.]+)[\D]*?([\d.]+|$)/i,hsla:/^((hsla)|hsl)[\D]+([\d.]+)[\D]+([\d.]+)[\D]+([\d.]+)[\D]*?([\d.]+|$)/i,hsva:/^((hsva)|hsv)[\D]+([\d.]+)[\D]+([\d.]+)[\D]+([\d.]+)[\D]*?([\d.]+|$)/i,hexa:/^#?(([\dA-Fa-f]{3,4})|([\dA-Fa-f]{6})|([\dA-Fa-f]{8}))$/i},o=t=>t.map((t=>/^(|\d+)\.\d+|\d+$/.test(t)?Number(t):void 0));let n;t:for(const i in e){if(!(n=e[i].exec(t)))continue;const s=t=>!!n[2]==("number"==typeof t);switch(i){case"cmyk":{const[,t,e,s,r]=o(n);if(t>100||e>100||s>100||r>100)break t;return{values:y(t,e,s,r),type:i}}case"rgba":{const[,,,t,e,r,a]=o(n);if(t>255||e>255||r>255||a<0||a>1||!s(a))break t;return{values:[...b(t,e,r),a],a,type:i}}case"hexa":{let[,t]=n;4!==t.length&&3!==t.length||(t=t.split("").map((t=>t+t)).join(""));const e=t.substring(0,6);let o=t.substring(6);return o=o?parseInt(o,16)/255:void 0,{values:[..._(e),o],a:o,type:i}}case"hsla":{const[,,,t,e,r,a]=o(n);if(t>360||e>100||r>100||a<0||a>1||!s(a))break t;return{values:[...g(t,e,r),a],a,type:i}}case"hsva":{const[,,,t,e,r,a]=o(n);if(t>360||e>100||r>100||a<0||a>1||!s(a))break t;return{values:[t,e,r,a],a,type:i}}}}return{values:null,type:null}}function A(t=0,e=0,o=0,n=1){const i=(t,e)=>(o=-1)=>e(~o?t.map((t=>Number(t.toFixed(o)))):t),s={h:t,s:e,v:o,a:n,toHSVA(){const t=[s.h,s.s,s.v,s.a];return t.toString=i(t,(t=>`hsva(${t[0]}, ${t[1]}%, ${t[2]}%, ${s.a})`)),t},toHSLA(){const t=[...v(s.h,s.s,s.v),s.a];return t.toString=i(t,(t=>`hsla(${t[0]}, ${t[1]}%, ${t[2]}%, ${s.a})`)),t},toRGBA(){const t=[...f(s.h,s.s,s.v),s.a];return t.toString=i(t,(t=>`rgba(${t[0]}, ${t[1]}, ${t[2]}, ${s.a})`)),t},toCMYK(){const t=function(t,e,o){const n=f(t,e,o),i=n[0]/255,s=n[1]/255,r=n[2]/255,a=u(1-i,1-s,1-r);return[100*(1===a?0:(1-i-a)/(1-a)),100*(1===a?0:(1-s-a)/(1-a)),100*(1===a?0:(1-r-a)/(1-a)),100*a]}(s.h,s.s,s.v);return t.toString=i(t,(t=>`cmyk(${t[0]}%, ${t[1]}%, ${t[2]}%, ${t[3]}%)`)),t},toHEXA(){const t=function(t,e,o){return f(t,e,o).map((t=>m(t).toString(16).padStart(2,"0")))}(s.h,s.s,s.v),e=s.a>=1?"":Number((255*s.a).toFixed(0)).toString(16).toUpperCase().padStart(2,"0");return e&&t.push(e),t.toString=()=>`#${t.join("").toUpperCase()}`,t},clone:()=>A(s.h,s.s,s.v,s.a)};return s}const C=t=>Math.max(Math.min(t,1),0);function $(t){const e={options:Object.assign({lock:null,onchange:()=>0,onstop:()=>0},t),_keyboard(t){const{options:o}=e,{type:n,key:i}=t;if(document.activeElement===o.wrapper){const{lock:o}=e.options,s="ArrowUp"===i,r="ArrowRight"===i,a="ArrowDown"===i,l="ArrowLeft"===i;if("keydown"===n&&(s||r||a||l)){let n=0,i=0;"v"===o?n=s||r?1:-1:"h"===o?n=s||r?-1:1:(i=s?-1:a?1:0,n=l?-1:r?1:0),e.update(C(e.cache.x+.01*n),C(e.cache.y+.01*i)),t.preventDefault()}else i.startsWith("Arrow")&&(e.options.onstop(),t.preventDefault())}},_tapstart(t){i(document,["mouseup","touchend","touchcancel"],e._tapstop),i(document,["mousemove","touchmove"],e._tapmove),t.cancelable&&t.preventDefault(),e._tapmove(t)},_tapmove(t){const{options:o,cache:n}=e,{lock:i,element:s,wrapper:r}=o,a=r.getBoundingClientRect();let l=0,c=0;if(t){const e=t&&t.touches&&t.touches[0];l=t?(e||t).clientX:0,c=t?(e||t).clientY:0,l<a.left?l=a.left:l>a.left+a.width&&(l=a.left+a.width),c<a.top?c=a.top:c>a.top+a.height&&(c=a.top+a.height),l-=a.left,c-=a.top}else n&&(l=n.x*a.width,c=n.y*a.height);"h"!==i&&(s.style.left=`calc(${l/a.width*100}% - ${s.offsetWidth/2}px)`),"v"!==i&&(s.style.top=`calc(${c/a.height*100}% - ${s.offsetHeight/2}px)`),e.cache={x:l/a.width,y:c/a.height};const p=C(l/a.width),u=C(c/a.height);switch(i){case"v":return o.onchange(p);case"h":return o.onchange(u);default:return o.onchange(p,u)}},_tapstop(){e.options.onstop(),s(document,["mouseup","touchend","touchcancel"],e._tapstop),s(document,["mousemove","touchmove"],e._tapmove)},trigger(){e._tapmove()},update(t=0,o=0){const{left:n,top:i,width:s,height:r}=e.options.wrapper.getBoundingClientRect();"h"===e.options.lock&&(o=t),e._tapmove({clientX:n+s*t,clientY:i+r*o})},destroy(){const{options:t,_tapstart:o,_keyboard:n}=e;s(document,["keydown","keyup"],n),s([t.wrapper,t.element],"mousedown",o),s([t.wrapper,t.element],"touchstart",o,{passive:!1})}},{options:o,_tapstart:n,_keyboard:r}=e;return i([o.wrapper,o.element],"mousedown",n),i([o.wrapper,o.element],"touchstart",n,{passive:!1}),i(document,["keydown","keyup"],r),e}function k(t={}){t=Object.assign({onchange:()=>0,className:"",elements:[]},t);const e=i(t.elements,"click",(e=>{t.elements.forEach((o=>o.classList[e.target===o?"add":"remove"](t.className))),t.onchange(e),e.stopPropagation()}));return{destroy:()=>s(...e)}}const S={variantFlipOrder:{start:"sme",middle:"mse",end:"ems"},positionFlipOrder:{top:"tbrl",right:"rltb",bottom:"btrl",left:"lrbt"},position:"bottom",margin:8},O=(t,e,o)=>{const{container:n,margin:i,position:s,variantFlipOrder:r,positionFlipOrder:a}={container:document.documentElement.getBoundingClientRect(),...S,...o},{left:l,top:c}=e.style;e.style.left="0",e.style.top="0";const p=t.getBoundingClientRect(),u=e.getBoundingClientRect(),h={t:p.top-u.height-i,b:p.bottom+i,r:p.right+i,l:p.left-u.width-i},d={vs:p.left,vm:p.left+p.width/2+-u.width/2,ve:p.left+p.width-u.width,hs:p.top,hm:p.bottom-p.height/2-u.height/2,he:p.bottom-u.height},[m,f="middle"]=s.split("-"),v=a[m],b=r[f],{top:y,left:g,bottom:_,right:w}=n;for(const t of v){const o="t"===t||"b"===t,n=h[t],[i,s]=o?["top","left"]:["left","top"],[r,a]=o?[u.height,u.width]:[u.width,u.height],[l,c]=o?[_,w]:[w,_],[p,m]=o?[y,g]:[g,y];if(!(n<p||n+r>l))for(const r of b){const l=d[(o?"v":"h")+r];if(!(l<m||l+a>c))return e.style[s]=l-u[s]+"px",e.style[i]=n-u[i]+"px",t+r}}return e.style.left=l,e.style.top=c,null};function E(t,e,o){return e in t?Object.defineProperty(t,e,{value:o,enumerable:!0,configurable:!0,writable:!0}):t[e]=o,t}class L{constructor(t){E(this,"_initializingActive",!0),E(this,"_recalc",!0),E(this,"_nanopop",null),E(this,"_root",null),E(this,"_color",A()),E(this,"_lastColor",A()),E(this,"_swatchColors",[]),E(this,"_setupAnimationFrame",null),E(this,"_eventListener",{init:[],save:[],hide:[],show:[],clear:[],change:[],changestop:[],cancel:[],swatchselect:[]}),this.options=t=Object.assign({...L.DEFAULT_OPTIONS},t);const{swatches:e,components:o,theme:n,sliders:i,lockOpacity:s,padding:r}=t;["nano","monolith"].includes(n)&&!i&&(t.sliders="h"),o.interaction||(o.interaction={});const{preview:a,opacity:l,hue:c,palette:p}=o;o.opacity=!s&&l,o.palette=p||a||l||c,this._preBuild(),this._buildComponents(),this._bindEvents(),this._finalBuild(),e&&e.length&&e.forEach((t=>this.addSwatch(t)));const{button:u,app:h}=this._root;this._nanopop=((t,e,o)=>{const n="object"!=typeof t||t instanceof HTMLElement?{reference:t,popper:e,...o}:t;return{update(t=n){const{reference:e,popper:o}=Object.assign(n,t);if(!o||!e)throw new Error("Popper- or reference-element missing.");return O(e,o,n)}}})(u,h,{margin:r}),u.setAttribute("role","button"),u.setAttribute("aria-label",this._t("btn:toggle"));const d=this;this._setupAnimationFrame=requestAnimationFrame((function e(){if(!h.offsetWidth)return requestAnimationFrame(e);d.setColor(t.default),d._rePositioningPicker(),t.defaultRepresentation&&(d._representation=t.defaultRepresentation,d.setColorRepresentation(d._representation)),t.showAlways&&d.show(),d._initializingActive=!1,d._emit("init")}))}_preBuild(){const{options:t}=this;for(const e of["el","container"])t[e]=c(t[e]);this._root=(t=>{const{components:e,useAsButton:o,inline:n,appClass:i,theme:s,lockOpacity:r}=t.options,l=t=>t?"":'style="display:none" hidden',c=e=>t._t(e),p=a(`\n      <div :ref="root" class="pickr">\n\n        ${o?"":'<button type="button" :ref="button" class="pcr-button"></button>'}\n\n        <div :ref="app" class="pcr-app ${i||""}" data-theme="${s}" ${n?'style="position: unset"':""} aria-label="${c("ui:dialog")}" role="window">\n          <div class="pcr-selection" ${l(e.palette)}>\n            <div :obj="preview" class="pcr-color-preview" ${l(e.preview)}>\n              <button type="button" :ref="lastColor" class="pcr-last-color" aria-label="${c("btn:last-color")}"></button>\n              <div :ref="currentColor" class="pcr-current-color"></div>\n            </div>\n\n            <div :obj="palette" class="pcr-color-palette">\n              <div :ref="picker" class="pcr-picker"></div>\n              <div :ref="palette" class="pcr-palette" tabindex="0" aria-label="${c("aria:palette")}" role="listbox"></div>\n            </div>\n\n            <div :obj="hue" class="pcr-color-chooser" ${l(e.hue)}>\n              <div :ref="picker" class="pcr-picker"></div>\n              <div :ref="slider" class="pcr-hue pcr-slider" tabindex="0" aria-label="${c("aria:hue")}" role="slider"></div>\n            </div>\n\n            <div :obj="opacity" class="pcr-color-opacity" ${l(e.opacity)}>\n              <div :ref="picker" class="pcr-picker"></div>\n              <div :ref="slider" class="pcr-opacity pcr-slider" tabindex="0" aria-label="${c("aria:opacity")}" role="slider"></div>\n            </div>\n          </div>\n\n          <div class="pcr-swatches ${e.palette?"":"pcr-last"}" :ref="swatches"></div>\n\n          <div :obj="interaction" class="pcr-interaction" ${l(Object.keys(e.interaction).length)}>\n            <input :ref="result" class="pcr-result" type="text" spellcheck="false" ${l(e.interaction.input)} aria-label="${c("aria:input")}">\n\n            <input :arr="options" class="pcr-type" data-type="HEXA" value="${r?"HEX":"HEXA"}" type="button" ${l(e.interaction.hex)}>\n            <input :arr="options" class="pcr-type" data-type="RGBA" value="${r?"RGB":"RGBA"}" type="button" ${l(e.interaction.rgba)}>\n            <input :arr="options" class="pcr-type" data-type="HSLA" value="${r?"HSL":"HSLA"}" type="button" ${l(e.interaction.hsla)}>\n            <input :arr="options" class="pcr-type" data-type="HSVA" value="${r?"HSV":"HSVA"}" type="button" ${l(e.interaction.hsva)}>\n            <input :arr="options" class="pcr-type" data-type="CMYK" value="CMYK" type="button" ${l(e.interaction.cmyk)}>\n\n            <input :ref="save" class="pcr-save" value="${c("btn:save")}" type="button" ${l(e.interaction.save)} aria-label="${c("aria:btn:save")}">\n            <input :ref="cancel" class="pcr-cancel" value="${c("btn:cancel")}" type="button" ${l(e.interaction.cancel)} aria-label="${c("aria:btn:cancel")}">\n            <input :ref="clear" class="pcr-clear" value="${c("btn:clear")}" type="button" ${l(e.interaction.clear)} aria-label="${c("aria:btn:clear")}">\n          </div>\n        </div>\n      </div>\n    `),u=p.interaction;return u.options.find((t=>!t.hidden&&!t.classList.add("active"))),u.type=()=>u.options.find((t=>t.classList.contains("active"))),p})(this),t.useAsButton&&(this._root.button=t.el),t.container.appendChild(this._root.root)}_finalBuild(){const t=this.options,e=this._root;if(t.container.removeChild(e.root),t.inline){const o=t.el.parentElement;t.el.nextSibling?o.insertBefore(e.app,t.el.nextSibling):o.appendChild(e.app)}else t.container.appendChild(e.app);t.useAsButton?t.inline&&t.el.remove():t.el.parentNode.replaceChild(e.root,t.el),t.disabled&&this.disable(),t.comparison||(e.button.style.transition="none",t.useAsButton||(e.preview.lastColor.style.transition="none")),this.hide()}_buildComponents(){const t=this,e=this.options.components,o=(t.options.sliders||"v").repeat(2),[n,i]=o.match(/^[vh]+$/g)?o:[],s=()=>this._color||(this._color=this._lastColor.clone()),r={palette:$({element:t._root.palette.picker,wrapper:t._root.palette.palette,onstop:()=>t._emit("changestop","slider",t),onchange(o,n){if(!e.palette)return;const i=s(),{_root:r,options:a}=t,{lastColor:l,currentColor:c}=r.preview;t._recalc&&(i.s=100*o,i.v=100-100*n,i.v<0&&(i.v=0),t._updateOutput("slider"));const p=i.toRGBA().toString(0);this.element.style.background=p,this.wrapper.style.background=`\n                        linear-gradient(to top, rgba(0, 0, 0, ${i.a}), transparent),\n                        linear-gradient(to left, hsla(${i.h}, 100%, 50%, ${i.a}), rgba(255, 255, 255, ${i.a}))\n                    `,a.comparison?a.useAsButton||t._lastColor||l.style.setProperty("--pcr-color",p):(r.button.style.setProperty("--pcr-color",p),r.button.classList.remove("clear"));const u=i.toHEXA().toString();for(const{el:e,color:o}of t._swatchColors)e.classList[u===o.toHEXA().toString()?"add":"remove"]("pcr-active");c.style.setProperty("--pcr-color",p)}}),hue:$({lock:"v"===i?"h":"v",element:t._root.hue.picker,wrapper:t._root.hue.slider,onstop:()=>t._emit("changestop","slider",t),onchange(o){if(!e.hue||!e.palette)return;const n=s();t._recalc&&(n.h=360*o),this.element.style.backgroundColor=`hsl(${n.h}, 100%, 50%)`,r.palette.trigger()}}),opacity:$({lock:"v"===n?"h":"v",element:t._root.opacity.picker,wrapper:t._root.opacity.slider,onstop:()=>t._emit("changestop","slider",t),onchange(o){if(!e.opacity||!e.palette)return;const n=s();t._recalc&&(n.a=Math.round(100*o)/100),this.element.style.background=`rgba(0, 0, 0, ${n.a})`,r.palette.trigger()}}),selectable:k({elements:t._root.interaction.options,className:"active",onchange(e){t._representation=e.target.getAttribute("data-type").toUpperCase(),t._recalc&&t._updateOutput("swatch")}})};this._components=r}_bindEvents(){const{_root:t,options:e}=this,o=[i(t.interaction.clear,"click",(()=>this._clearColor())),i([t.interaction.cancel,t.preview.lastColor],"click",(()=>{this.setHSVA(...(this._lastColor||this._color).toHSVA(),!0),this._emit("cancel")})),i(t.interaction.save,"click",(()=>{!this.applyColor()&&!e.showAlways&&this.hide()})),i(t.interaction.result,["keyup","input"],(t=>{this.setColor(t.target.value,!0)&&!this._initializingActive&&(this._emit("change",this._color,"input",this),this._emit("changestop","input",this)),t.stopImmediatePropagation()})),i(t.interaction.result,["focus","blur"],(t=>{this._recalc="blur"===t.type,this._recalc&&this._updateOutput(null)})),i([t.palette.palette,t.palette.picker,t.hue.slider,t.hue.picker,t.opacity.slider,t.opacity.picker],["mousedown","touchstart"],(()=>this._recalc=!0),{passive:!0})];if(!e.showAlways){const n=e.closeWithKey;o.push(i(t.button,"click",(()=>this.isOpen()?this.hide():this.show())),i(document,"keyup",(t=>this.isOpen()&&(t.key===n||t.code===n)&&this.hide())),i(document,["touchstart","mousedown"],(e=>{this.isOpen()&&!l(e).some((e=>e===t.app||e===t.button))&&this.hide()}),{capture:!0}))}if(e.adjustableNumbers){const e={rgba:[255,255,255,1],hsva:[360,100,100,1],hsla:[360,100,100,1],cmyk:[100,100,100,100]};p(t.interaction.result,((t,o,n)=>{const i=e[this.getColorRepresentation().toLowerCase()];if(i){const e=i[n],s=t+(e>=100?1e3*o:o);return s<=0?0:Number((s<e?s:e).toPrecision(3))}return t}))}if(e.autoReposition&&!e.inline){let t=null;const n=this;o.push(i(window,["scroll","resize"],(()=>{n.isOpen()&&(e.closeOnScroll&&n.hide(),null===t?(t=setTimeout((()=>t=null),100),requestAnimationFrame((function e(){n._rePositioningPicker(),null!==t&&requestAnimationFrame(e)}))):(clearTimeout(t),t=setTimeout((()=>t=null),100)))}),{capture:!0}))}this._eventBindings=o}_rePositioningPicker(){const{options:t}=this;if(!t.inline){if(!this._nanopop.update({container:document.body.getBoundingClientRect(),position:t.position})){const t=this._root.app,e=t.getBoundingClientRect();t.style.top=(window.innerHeight-e.height)/2+"px",t.style.left=(window.innerWidth-e.width)/2+"px"}}}_updateOutput(t){const{_root:e,_color:o,options:n}=this;if(e.interaction.type()){const t=`to${e.interaction.type().getAttribute("data-type")}`;e.interaction.result.value="function"==typeof o[t]?o[t]().toString(n.outputPrecision):""}!this._initializingActive&&this._recalc&&this._emit("change",o,t,this)}_clearColor(t=!1){const{_root:e,options:o}=this;o.useAsButton||e.button.style.setProperty("--pcr-color","rgba(0, 0, 0, 0.15)"),e.button.classList.add("clear"),o.showAlways||this.hide(),this._lastColor=null,this._initializingActive||t||(this._emit("save",null),this._emit("clear"))}_parseLocalColor(t){const{values:e,type:o,a:n}=w(t),{lockOpacity:i}=this.options,s=void 0!==n&&1!==n;return e&&3===e.length&&(e[3]=void 0),{values:!e||i&&s?null:e,type:o}}_t(t){return this.options.i18n[t]||L.I18N_DEFAULTS[t]}_emit(t,...e){this._eventListener[t].forEach((t=>t(...e,this)))}on(t,e){return this._eventListener[t].push(e),this}off(t,e){const o=this._eventListener[t]||[],n=o.indexOf(e);return~n&&o.splice(n,1),this}addSwatch(t){const{values:e}=this._parseLocalColor(t);if(e){const{_swatchColors:t,_root:o}=this,n=A(...e),s=r(`<button type="button" style="--pcr-color: ${n.toRGBA().toString(0)}" aria-label="${this._t("btn:swatch")}"/>`);return o.swatches.appendChild(s),t.push({el:s,color:n}),this._eventBindings.push(i(s,"click",(()=>{this.setHSVA(...n.toHSVA(),!0),this._emit("swatchselect",n),this._emit("change",n,"swatch",this)}))),!0}return!1}removeSwatch(t){const e=this._swatchColors[t];if(e){const{el:o}=e;return this._root.swatches.removeChild(o),this._swatchColors.splice(t,1),!0}return!1}applyColor(t=!1){const{preview:e,button:o}=this._root,n=this._color.toRGBA().toString(0);return e.lastColor.style.setProperty("--pcr-color",n),this.options.useAsButton||o.style.setProperty("--pcr-color",n),o.classList.remove("clear"),this._lastColor=this._color.clone(),this._initializingActive||t||this._emit("save",this._color),this}destroy(){cancelAnimationFrame(this._setupAnimationFrame),this._eventBindings.forEach((t=>s(...t))),Object.keys(this._components).forEach((t=>this._components[t].destroy()))}destroyAndRemove(){this.destroy();const{root:t,app:e}=this._root;t.parentElement&&t.parentElement.removeChild(t),e.parentElement.removeChild(e),Object.keys(this).forEach((t=>this[t]=null))}hide(){return!!this.isOpen()&&(this._root.app.classList.remove("visible"),this._emit("hide"),!0)}show(){return!this.options.disabled&&!this.isOpen()&&(this._root.app.classList.add("visible"),this._rePositioningPicker(),this._emit("show",this._color),this)}isOpen(){return this._root.app.classList.contains("visible")}setHSVA(t=360,e=0,o=0,n=1,i=!1){const s=this._recalc;if(this._recalc=!1,t<0||t>360||e<0||e>100||o<0||o>100||n<0||n>1)return!1;this._color=A(t,e,o,n);const{hue:r,opacity:a,palette:l}=this._components;return r.update(t/360),a.update(n),l.update(e/100,1-o/100),i||this.applyColor(),s&&this._updateOutput(),this._recalc=s,!0}setColor(t,e=!1){if(null===t)return this._clearColor(e),!0;const{values:o,type:n}=this._parseLocalColor(t);if(o){const t=n.toUpperCase(),{options:i}=this._root.interaction,s=i.find((e=>e.getAttribute("data-type")===t));if(s&&!s.hidden)for(const t of i)t.classList[t===s?"add":"remove"]("active");return!!this.setHSVA(...o,e)&&this.setColorRepresentation(t)}return!1}setColorRepresentation(t){return t=t.toUpperCase(),!!this._root.interaction.options.find((e=>e.getAttribute("data-type").startsWith(t)&&!e.click()))}getColorRepresentation(){return this._representation}getColor(){return this._color}getSelectedColor(){return this._lastColor}getRoot(){return this._root}disable(){return this.hide(),this.options.disabled=!0,this._root.button.classList.add("disabled"),this}enable(){return this.options.disabled=!1,this._root.button.classList.remove("disabled"),this}}return E(L,"utils",o),E(L,"version","1.8.2"),E(L,"I18N_DEFAULTS",{"ui:dialog":"color picker dialog","btn:toggle":"toggle color picker dialog","btn:swatch":"color swatch","btn:last-color":"use previous color","btn:save":"Save","btn:cancel":"Cancel","btn:clear":"Clear","aria:btn:save":"save and close","aria:btn:cancel":"cancel and close","aria:btn:clear":"clear and close","aria:input":"color input field","aria:palette":"color selection area","aria:hue":"hue selection slider","aria:opacity":"selection slider"}),E(L,"DEFAULT_OPTIONS",{appClass:null,theme:"classic",useAsButton:!1,padding:8,disabled:!1,comparison:!0,closeOnScroll:!1,outputPrecision:0,lockOpacity:!1,autoReposition:!0,container:"body",components:{interaction:{}},i18n:{},swatches:null,inline:!1,sliders:null,default:"#42445a",defaultRepresentation:null,position:"bottom-middle",adjustableNumbers:!0,showAlways:!1,closeWithKey:"Escape"}),E(L,"create",(t=>new L(t))),e=e.default})()}));

/**
 * HotKeys | https://github.com/jeresig/jquery.hotkeys
 */
!function(t){function e(e){if("string"==typeof e.data&&(e.data={keys:e.data}),e.data&&e.data.keys&&"string"==typeof e.data.keys){var a=e.handler,s=e.data.keys.toLowerCase().split(" ");e.handler=function(e){if(this===e.target||!(t.hotkeys.options.filterInputAcceptingElements&&t.hotkeys.textInputTypes.test(e.target.nodeName)||t.hotkeys.options.filterContentEditable&&t(e.target).attr("contenteditable")||t.hotkeys.options.filterTextInputs&&t.inArray(e.target.type,t.hotkeys.textAcceptingInputTypes)>-1)){var n="keypress"!==e.type&&t.hotkeys.specialKeys[e.which],i=String.fromCharCode(e.which).toLowerCase(),r="",o={};t.each(["alt","ctrl","shift"],function(t,a){e[a+"Key"]&&n!==a&&(r+=a+"+")}),e.metaKey&&!e.ctrlKey&&"meta"!==n&&(r+="meta+"),e.metaKey&&"meta"!==n&&r.indexOf("alt+ctrl+shift+")>-1&&(r=r.replace("alt+ctrl+shift+","hyper+")),n?o[r+n]=!0:(o[r+i]=!0,o[r+t.hotkeys.shiftNums[i]]=!0,"shift+"===r&&(o[t.hotkeys.shiftNums[i]]=!0));for(var p=0,l=s.length;p<l;p++)if(o[s[p]])return a.apply(this,arguments)}}}}t.hotkeys={version:"0.2.0",specialKeys:{8:"backspace",9:"tab",10:"return",13:"return",16:"shift",17:"ctrl",18:"alt",19:"pause",20:"capslock",27:"esc",32:"space",33:"pageup",34:"pagedown",35:"end",36:"home",37:"left",38:"up",39:"right",40:"down",45:"insert",46:"del",59:";",61:"=",96:"0",97:"1",98:"2",99:"3",100:"4",101:"5",102:"6",103:"7",104:"8",105:"9",106:"*",107:"+",109:"-",110:".",111:"/",112:"f1",113:"f2",114:"f3",115:"f4",116:"f5",117:"f6",118:"f7",119:"f8",120:"f9",121:"f10",122:"f11",123:"f12",144:"numlock",145:"scroll",173:"-",186:";",187:"=",188:",",189:"-",190:".",191:"/",192:"`",219:"[",220:"\\",221:"]",222:"'"},shiftNums:{"`":"~",1:"!",2:"@",3:"#",4:"$",5:"%",6:"^",7:"&",8:"*",9:"(",0:")","-":"_","=":"+",";":": ","'":'"',",":"<",".":">","/":"?","\\":"|"},textAcceptingInputTypes:["text","password","number","email","url","range","date","month","week","time","datetime","datetime-local","search","color","tel"],textInputTypes:/textarea|input|select/i,options:{filterInputAcceptingElements:!0,filterTextInputs:!0,filterContentEditable:!0}},t.each(["keydown","keyup","keypress"],function(){t.event.special[this]={add:e}})}(jQuery||this.jQuery||window.jQuery);

/**
 * UFO Editor
 */
ufo.register(null, function ( ) {
    'use strict';
    
    let $self;

    const $Rich            = $(".ufo-content-editor");
    const $ContentDrop     = $(".ufo-element-droppable .ufo-create-child-container");
    const $colorPickerI18n = {
        'btn:save': ufo.lng('Confirm'),
        'btn:cancel': ufo.lng('Cancel'),
        'btn:clear': ufo.lng('Clear')
    };
    const $saver           = {
        "content_droppable": false,
        "now_content_dragged": "",
        "now_info_widget": {
            "type": null
        },
        "drop_over": false,
        "widget_saver": {},
        "custom_types": [],
        "setting_page": {
            "photo"  : [],
            "link"   : "",
            "title"  : "",
            "desc"   : "",
            "type"   : ufo.GET("type") ?? "page",
            "status" : 1,
            "pass"   : "",
            "tags"   : "",
            "category" : [],
            "category_restore": []
        },
        "codes": {
            "php": "",
            "js" : "",
            "css": ""
        }
    };

    let $loader_template  = `<div class="ufo-editor-loader flex-direction-column"><div class="ufo-loader-box"><span></span><span></span><span></span></div></div>`;
    let $widgets          = [];
    let $shortcodes       = [];
    let canvas_history    = [];
    let s_history         = true;
    let cur_history_index = 0;
    let eventDragged      = $("<div>");

    let execFontSize = function (size, unit) {
        document.execCommand('insertHTML', false, $('<span/>', {
            'text': document.getSelection()
        }).css('font-size', size + unit).prop('outerHTML'));
    };

    function rtlToolbar ( ) {
        return [
            {
                "setting": {
                    "icon": "ufo-icon-settings",
                    "default": false,
                    "function": ( ) => {
                        const setting = $(`.ufo-toolbar-column button[data-cmd="setting"]`);
                        $(window).resize(function() {
                            if($(window).width() >= 1024) {
                                setting.parent(".ufo-toolbar-column").hide();
                            } else {
                                setting.parent(".ufo-toolbar-column").show();
                            }
                        }).resize();
                    },
                    "onclick": ( ) => {
                        $(`.side`).toggleClass("none");
                    }
                }
            },
            {
                "redo": {
                    "icon": "ufo-icon-redo",
                    "default": false,
                    "onclick": function () {
                        $self.history_redo();
                    }
                },
                "undo": {
                    "icon": "ufo-icon-undo",
                    "default": false,
                    "onclick": function () {
                        $self.history_undo();
                    }
                }
            },
            {
                "justifyRight": {
                    "icon": "ufo-icon-align-right",
                    "function": function () {
                        const btn = $(`.ufo-toolbar-column button[data-cmd="justifyRight"]`);
                        $self.hasStateActive(btn, "justifyRight");
                    }
                },
                "justifyCenter": {
                    "icon": "ufo-icon-align-center",
                    "function": function () {
                        const btn = $(`.ufo-toolbar-column button[data-cmd="justifyCenter"]`);
                        $self.hasStateActive(btn, "justifyCenter");
                    }
                },
                "justifyLeft": {
                    "icon": "ufo-icon-align-left",
                    "function": function () {
                        const btn = $(`.ufo-toolbar-column button[data-cmd="justifyLeft"]`);
                        $self.hasStateActive(btn, "justifyLeft");
                    }
                },
                "bold": {
                    "icon": "ufo-icon-bold font-size-17px",
                    "function": function () {
                        const btn = $(`.ufo-toolbar-column button[data-cmd="bold"]`);
                        $self.hasStateActive(btn, "bold");
                    }
                },
                "italic": {
                    "icon": "ufo-icon-italic font-size-17px",
                    "function": function () {
                        const btn = $(`.ufo-toolbar-column button[data-cmd="italic"]`);
                        $self.hasStateActive(btn, "italic");
                    }
                },
                "underline": {
                    "icon": "ufo-icon-underline",
                    "function": function () {
                        const btn = $(`.ufo-toolbar-column button[data-cmd="underline"]`);
                        $self.hasStateActive(btn, "underline");
                    }
                }
            },
            {
                "link": {
                    "icon": "ufo-icon-link",
                    "onclick": function ( ) {
                        let Text = getSelection().toString();
                        if ( Text.length === 0 ) return;

                        $self.execCommand("insertHTML", `<span class="ufo-fake-select" style="background: #00a6ff;color: white;padding: 0 5px;">${Text}</span>`);

                        $.ufo_dialog({
                            title: `<span class="font-size-16px">${ufo.lng("Add link")}</span>`,
                            content: `
                                   <input value="" class="ufo-input-link form-control font-size-16px" dir="ltr" placeholder="${ufo.lng("link")}">
                                   <select class="ufo-select-link-type form-control mt-5">
                                      <option value="_self">${ufo.lng("same page")}</option>
                                      <option value="_blank">${ufo.lng("new window")}</option>
                                   </select>
                                `,
                            options: {
                                okText: ufo.lng("add"),
                                cancel: true,
                                callbacks: {
                                    okClick ( ) {
                                        $(".ufo-fake-select").replaceWith(`<a href="${$(".ufo-input-link").val()}" target="${$(`.ufo-select-link-type`).val()}">${(Text.length == 0 ? $(".ufo-input-link").val() : Text)}</a>`);
                                        $self.save_history();
                                        this.hide();
                                    },
                                    cancelClick ( ) {
                                        $("span.ufo-fake-select").replaceWith(Text);this.hide();
                                    }
                                }
                            }
                        });
                    }
                },
                "copy": {
                    "icon": "ufo-icon-copy",
                },
                "cut": {
                    "icon": "ufo-icon-scissors"
                },
                "fontsize": {
                    "class": "cursor-pointer font-size-29px ufo-select-fontsize ufo-icon-font-size",
                    "function": function () {
                        const fontSize = $(`.ufo-select-fontsize`);
                        fontSize.click(function ( ) {
                            $.ufo_dialog({
                                title: ufo.lng("Font size"),
                                content: function ( list = [] ) {
                                    for (let i = 1; i < 71; i++) {
                                        list.push({
                                            id: i, size: i
                                        })
                                    }
                                    return list;
                                }(),
                                options: {
                                    selection: true,
                                    textField: 'id',
                                    valueField: 'size',
                                    callbacks: {
                                        itemSelect: function (e, i) {
                                            execFontSize(i.size, 'px');
                                        }
                                    }
                                }
                            });
                        });
                    }
                }
            },
            {
                "color": {
                    "class": "ufo-box-color",
                    "default": false
                }
            },
            {
                "code": {
                    "class": "ufo-icon-code-2 font-size-28px",
                    "function": function () {
                        $(`.ufo-toolbar-column button[data-cmd="code"]`).unbind().click(function () {
                            $self.codeEditor();
                        });
                    }
                }
            }
        ];
    }
    function ltrToolbar ( ) {
        return [
            {
                "setting": {
                    "icon": "ufo-icon-settings",
                    "default": false,
                    "function": ( ) => {
                        const setting = $(`.ufo-toolbar-column button[data-cmd="setting"]`);
                        $(window).resize(function() {
                            if($(window).width() >= 1024) {
                                setting.parent(".ufo-toolbar-column").hide();
                            } else {
                                setting.parent(".ufo-toolbar-column").show();
                            }
                        }).resize();
                    },
                    "onclick": ( ) => {
                        $(`.side`).toggleClass("none");
                    }
                }
            },
            {
                "undo": {
                    "icon": "ufo-icon-undo",
                    "default": false,
                    "onclick": function () {
                        $self.history_undo();
                    }
                },
                "redo": {
                    "icon": "ufo-icon-redo",
                    "default": false,
                    "onclick": function () {
                        $self.history_redo();
                    }
                }
            },
            {
                "justifyLeft": {
                    "icon": "ufo-icon-align-left",
                    "function": function () {
                        const btn = $(`.ufo-toolbar-column button[data-cmd="justifyLeft"]`);
                        $self.hasStateActive(btn, "justifyLeft");
                    }
                },
                "justifyCenter": {
                    "icon": "ufo-icon-align-center",
                    "function": function () {
                        const btn = $(`.ufo-toolbar-column button[data-cmd="justifyCenter"]`);
                        $self.hasStateActive(btn, "justifyCenter");
                    }
                },
                "justifyRight": {
                    "icon": "ufo-icon-align-right",
                    "function": function () {
                        const btn = $(`.ufo-toolbar-column button[data-cmd="justifyRight"]`);
                        $self.hasStateActive(btn, "justifyRight");
                    }
                },
                "bold": {
                    "icon": "ufo-icon-bold font-size-17px",
                    "function": function () {
                        const btn = $(`.ufo-toolbar-column button[data-cmd="bold"]`);
                        $self.hasStateActive(btn, "bold");
                    }
                },
                "italic": {
                    "icon": "ufo-icon-italic font-size-17px",
                    "function": function () {
                        const btn = $(`.ufo-toolbar-column button[data-cmd="italic"]`);
                        $self.hasStateActive(btn, "italic");
                    }
                },
                "underline": {
                    "icon": "ufo-icon-underline",
                    "function": function () {
                        const btn = $(`.ufo-toolbar-column button[data-cmd="underline"]`);
                        $self.hasStateActive(btn, "underline");
                    }
                }
            },
            {
                "link": {
                    "icon": "ufo-icon-link",
                    "onclick": function ( ) {
                        let Text = getSelection().toString();
                        if ( Text.length === 0 ) return;

                        $self.execCommand("insertHTML", `<span class="ufo-fake-select" style="background: #00a6ff;color: white;padding: 0 5px;">${Text}</span>`);

                        $.ufo_dialog({
                            title: `<span class="font-size-16px">${ufo.lng("Add link")}</span>`,
                            content: `
                                   <input value="" class="ufo-input-link form-control font-size-16px" dir="ltr" placeholder="${ufo.lng("link")}">
                                   <select class="ufo-select-link-type form-control mt-5">
                                      <option value="_self">${ufo.lng("same page")}</option>
                                      <option value="_blank">${ufo.lng("new window")}</option>
                                   </select>
                                `,
                            options: {
                                okText: ufo.lng("add"),
                                cancel: true,
                                callbacks: {
                                    okClick ( ) {
                                        $(".ufo-fake-select").replaceWith(`<a href="${$(".ufo-input-link").val()}" target="${$(`.ufo-select-link-type`).val()}">${(Text.length == 0 ? $(".ufo-input-link").val() : Text)}</a>`);
                                        $self.save_history();
                                        this.hide();
                                    },
                                    cancelClick ( ) {
                                        $("span.ufo-fake-select").replaceWith(Text);this.hide();
                                    }
                                }
                            }
                        });
                    }
                },
                "copy": {
                    "icon": "ufo-icon-copy",
                },
                "cut": {
                    "icon": "ufo-icon-scissors"
                },
                "fontsize": {
                    "class": "cursor-pointer font-size-29px ufo-select-fontsize ufo-icon-font-size",
                    "function": function () {
                        const fontSize = $(`.ufo-select-fontsize`);
                        fontSize.click(function ( ) {
                            $.ufo_dialog({
                                title: ufo.lng("Font size"),
                                content: function ( list = [] ) {
                                    for (let i = 1; i < 71; i++) {
                                        list.push({
                                            id: i, size: i
                                        })
                                    }
                                    return list;
                                }(),
                                options: {
                                    selection: true,
                                    textField: 'id',
                                    valueField: 'size',
                                    callbacks: {
                                        itemSelect: function (e, i) {
                                            execFontSize(i.size, 'px');
                                        }
                                    }
                                }
                            });
                        });
                    }
                }
            },
            {
                "color": {
                    "class": "ufo-box-color",
                    "default": false
                }
            },
            {
                "code": {
                    "class": "ufo-icon-code-2 font-size-28px",
                    "function": function () {
                        $(`.ufo-toolbar-column button[data-cmd="code"]`).unbind().click(function () {
                            $self.codeEditor();
                        });
                    }
                }
            }
        ];
    }

    const $Toolbar    = (ufo.dir === "rtl" ? rtlToolbar() : ltrToolbar());
    const $Animations = ["bounce", "flash", "pulse", "rubberBand", "shake", "headShake", "swing", "tada", "wobble", "jello", "heartBeat", "bounceIn", "bounceInDown", "bounceInLeft", "bounceInRight", "bounceInUp", "bounceOut", "bounceOutDown", "bounceOutLeft", "bounceOutRight", "bounceOutUp", "fadeIn", "fadeInDown", "fadeInDownBig", "fadeInLeft", "fadeInLeftBig", "fadeInRight", "fadeInRightBig", "fadeInUp", "fadeInUpBig", "fadeOut", "fadeOutDown", "fadeOutDownBig", "fadeOutLeft", "fadeOutLeftBig", "fadeOutRight", "fadeOutRightBig", "fadeOutUp", "fadeOutUpBig", "flipInX", "flipInY", "flipOutX", "flipOutY", "lightSpeedIn", "lightSpeedOut", "rotateIn", "rotateInDownLeft", "rotateInDownRight", "rotateInUpLeft", "rotateInUpRight", "rotateOut", "rotateOutDownLeft", "rotateOutDownRight", "rotateOutUpLeft", "rotateOutUpRight", "hinge", "jackInTheBox", "rollIn", "rollOut", "zoomIn", "zoomInDown", "zoomInLeft", "zoomInRight", "zoomInUp", "zoomOut", "zoomOutDown", "zoomOutLeft", "zoomOutRight", "zoomOutUp", "slideInDown", "slideInLeft", "slideInRight", "slideInUp", "slideOutDown", "slideOutLeft", "slideOutRight", "slideOutUp"];
    const $StatusPage = [ufo.lng("draft"), ufo.lng("published"), ufo.lng("hidden"), ufo.lng("encrypted")];

    loader();

    function loader ($remove = false) {
        if ($remove) {
            $(".ufo-layer-lock").remove();
        } else {
            $("body").prepend(`<div class="ufo-layer-lock">${$loader_template}</div>`);
        }
    }

    $(function ( ) {
        const methods = {
            init ( ) {
                try {
                    $self = this;

                    $self.filters();

                    /**
                     * Run all init FNS
                     */
                    (!ufo.isNULL(ufo.get("ufo_editor_init_fns")) ? ufo.get("ufo_editor_init_fns") : []).map(i => i());

                    $self.addSideElements();
                    $self.get_data_widgets();

                    $self.setupToolbar();
                    $self.EndEditWidgets();
                    $self.config();

                    $self.addColumns();

                    $self.mobile();

                    $self.response();
                    window.onresize = $self.response;
                } catch (e) {
                    console.log("Error : " + e);
                }
            },
            restore ( ) {
                const $this = this;
                $.fun().do({
                    name : "req",
                    param: {
                        data: {
                            callback: "page_editor",
                            action  : "get",
                            page    : ufo.GET("page")
                        },
                        dataType: "json",
                        done ( result ) {
                            if ( result.status === 200 ) {
                                result = result.message;

                                /**
                                 * Set Info
                                 */
                                $saver.setting_page.title    = result.title;
                                $saver.setting_page.link     = result.link;
                                $saver.setting_page.desc     = result.short_desc;
                                $saver.setting_page.photo    = ufo.isJSON(result.photo) ? JSON.parse(result.photo) : (Array.isArray(result.photo) ? result.photo : []);
                                $saver.setting_page.type     = result.type;
                                $saver.setting_page.status   = result.status;
                                $saver.setting_page.tags     = result.tags;
                                $saver.setting_page.category_restore = $.map(result.category, ( v, k ) => {
                                    if (typeof v === "string") return v
                                });
                                $saver.setting_page.category = $.map(result.category, ( v, k ) => {return Number(k)});
                                $saver.setting_page.pass     = result.password;

                                document.title = ufo.lng("Edit") + " - " + result.title;

                                /**
                                 * Set Codes
                                 */
                                $saver.codes.php = result.php;
                                $saver.codes.js  = result.js;
                                $saver.codes.css = result.css;

                                /**
                                 * Set Setting
                                 */
                                $saver.widget_saver = result.setting;

                                /**
                                 * Set Content
                                 */
                                $(`.ufo-content-editor`).html(result.content);

                                $this.init();

                                $this.save_history();
                                $this.fixProblemHistory();
                            } else {
                                $.ufo_dialog({
                                    content: result.message
                                })
                            }
                        },
                        error ( ) {
                            $.ufo_dialog({
                                content: ufo.lng("Connection error")
                            })
                        }
                    }
                });
            },

            filters ( ) {
                $.fun().apply({
                    name: "ufo-editor-accordion",
                    method: $self.accordion
                });
                $.fun().apply({
                    name: "ufo-editor-color-picker",
                    method: function ( config ) {
                        return $self.colorPicker(config.target, config);
                    }
                });
                $.fun().apply({
                    name: "ufo_editor_add_type",
                    method: function ( {title, type} ) {
                        if ( typeof title !== "undefined" && typeof type !== "undefined" ) {
                            let has = false;
                            $.map($saver.custom_types, ( v, k ) => {
                                if ( v.name === title ) {
                                    has = true;
                                }
                            });
                            if ( !has ) {
                                $saver.custom_types.push({
                                    name: title,
                                    id: type
                                })
                            }
                        }
                    }
                })
            },

            sideTabs ( ) {
                return {
                    "base": [
                        {
                            "tab": "widgets",
                            "title": ufo.lng("Widgets"),
                            "active": true
                        },
                        {
                            "tab": "setting",
                            "title": ufo.lng("Setting")
                        }
                    ],
                    "edit_widget": [
                        {
                            "tab": "edit-widget",
                            "title": ufo.lng("Edit"),
                            "active": true
                        },
                        {
                            "tab": "widget-setting",
                            "title": ufo.lng("Advanced")
                        }
                    ]
                };
            },
            addSideElements ( ) {
                $(`.side`).remove();
                $(`.ufo-p-editor-layout`).prepend(`<div class="side"><ul class="side-tabs"></ul><div class="ufo-side-content-container"></div></div>`);
            },
            renderSideTabs ( ) {
                const tabs  = $self.sideTabs();
                const $tabs = {};

                $(`.side-tabs`).empty();

                $.each(tabs["base"], (k, v) => {
                    const Active = (typeof v.active !== "undefined");
                    $tabs[v.tab] = Active;
                    $(`.side-tabs`).append(`<li class="${(Active ? "active" : "")}" data-tab="${v.tab}">${v.title}</li>`);
                });

                $self.tabsClicker();

                $.each($tabs, (k, v) => {
                    $(`.ufo-side-content-container`).append($self.json2html($self.sideContent(v)[k]));
                });

                $self.AdvanceData();
                $.fun().do({name: "ufo_editor_do_draggable"});
            },
            tabsClicker ( ) {
                $(".side-tabs li").unbind().click(function () {
                    $(".side-tabs li").removeClass("active");
                    $(this).addClass("active"); $(".ufo-side-tab-content").removeClass("active");
                    $(`.ufo-side-tab-content[data-tab="${$(this).data("tab")}"]`).addClass("active");
                });
            },
            sideContent ( active = false, html = [] ) {
                return {
                    "widgets": {
                        "tag": "div",
                        "html": [
                            {
                                "tag": "button",
                                "html": [ufo.lng("shortcode")],
                                "attrs": {
                                    "class": "ufo-editor-accordion"
                                }
                            },
                            {
                                "tag": "div",
                                "html": [
                                    {
                                        "tag": "div",
                                        "html": [
                                            {
                                                "tag": "input",
                                                "attrs": {
                                                    "type": "search",
                                                    "class": "search width-100-cent form-control search-shortcodes",
                                                    "placeholder": ufo.lng("search")
                                                }
                                            },
                                        ],
                                        "attrs": {
                                            "class": "width-100-cent flex flex-center p-5px"
                                        }
                                    },
                                    {
                                        "tag": "ul",
                                        "html": function ( list = [] ) {
                                            $.each($shortcodes, (k, v) => {
                                                list.push({
                                                    "tag": "li",
                                                    "html": [
                                                        {
                                                            "tag": "div",
                                                            "html": [
                                                                {
                                                                    "tag": "i",
                                                                    "attrs": {
                                                                        "class": "ufo-icon-draggable ufo-drag-handle-shortcode"
                                                                    }
                                                                },
                                                                {
                                                                    "tag": "span",
                                                                    "html": v.title
                                                                }
                                                            ]
                                                        }
                                                    ],
                                                    "attrs": {
                                                        "class": "ufo-shortcodes-widget",
                                                        "data-shortcode": v.code
                                                    }
                                                });
                                            });
                                            return list;
                                        }(),
                                        "attrs": {
                                            "class": "ufo-shortcodes-widgets"
                                        }
                                    },
                                ],
                                "attrs": {
                                    "class": "ufo-editor-accordion-content"
                                }
                            },
                            {
                                "tag": "button",
                                "html": [ufo.lng("widget")],
                                "attrs": {
                                    "class": "ufo-editor-accordion active"
                                }
                            },
                            {
                                "tag": "div",
                                "html": [
                                    {
                                        "tag": "div",
                                        "html": [
                                            {
                                                "tag": "input",
                                                "attrs": {
                                                    "type": "search",
                                                    "class": "search width-100-cent form-control search-widgets",
                                                    "placeholder": ufo.lng("search")
                                                }
                                            },
                                        ],
                                        "attrs": {
                                            "class": "width-100-cent flex flex-center p-5px"
                                        }
                                    },
                                    {
                                        "tag": "div",
                                        "html": $self.render_item_widgets(),
                                        "attrs": {
                                            "class": "grid-2 width-100-cent ufo-widget-container"
                                        }
                                    },
                                ],
                                "attrs": {
                                    "class": "ufo-editor-accordion-content"
                                }
                            },
                        ],
                        "attrs": {
                            "class": "ufo-side-tab-content " + (active ? "active" : ""),
                            "data-tab": "widgets"
                        }
                    },
                    "setting": {
                        "tag": "div",
                        "html": [
                            {
                                "tag": "button",
                                "html": ufo.lng("Publish"),
                                "attrs": {
                                    "class": "btn btn-success mb-10 mr-5 mt-5 ufo-publish-page"
                                }
                            },
                            {
                                "tag": "div",
                                "attrs": {"class": "ufo-photo-box-page"},
                                "html": [
                                    {
                                        "tag": "div",
                                        "attrs": {"class": "ufo-select-img-items"}
                                    }
                                ]
                            },
                            {
                                "tag": "span",
                                "attrs": {"class": "mr-5 ml-5 mt-10 db"},
                                "html": [
                                    {
                                        "tag": "span",
                                        "html": ufo.lng("title"),
                                        "attrs": {
                                            "class": "font-size-15px db"
                                        }
                                    },
                                    {
                                        "tag": "input",
                                        "attrs": {
                                            "class": "form-control mt-5 ufo-page-title",
                                            "value": $saver.setting_page.title,
                                            "placeholder": ufo.lng("title")
                                        }
                                    }
                                ]
                            },
                            {
                                "tag": "span",
                                "attrs": {"class": "mr-5 ml-5 mt-10 db"},
                                "html": [
                                    {
                                        "tag": "span",
                                        "html": ufo.lng("link"),
                                        "attrs": {
                                            "class": "font-size-15px db"
                                        }
                                    },
                                    {
                                        "tag": "input",
                                        "attrs": {
                                            "class": "form-control mt-5 ufo-page-link",
                                            "value": $saver.setting_page.link,
                                            "placeholder": ufo.lng("link")
                                        }
                                    }
                                ]
                            },
                            {
                                "tag": "span",
                                "attrs": {"class": "mr-5 ml-5 mt-10 db"},
                                "html": [
                                    {
                                        "tag": "span",
                                        "html": ufo.lng("Description"),
                                        "attrs": {
                                            "class": "font-size-15px db"
                                        }
                                    },
                                    {
                                        "tag": "textarea",
                                        "html": [$saver.setting_page.desc],
                                        "attrs": {
                                            "class": "form-control mt-5 p-10px ufo-short-description",
                                            "placeholder": ufo.lng("Description"),
                                            "style": "resize: none;height:125px"
                                        }
                                    }
                                ]
                            },
                            {
                                "tag": "span",
                                "attrs": {"class": "mr-5 ml-5 mt-10 db ufo-select-type"},
                                "html": [
                                    {
                                        "tag": "span",
                                        "html": ufo.lng("type"),
                                        "attrs": {
                                            "class": "font-size-15px db"
                                        }
                                    },
                                    {
                                        "tag": "input",
                                        "attrs": {
                                            "class": "form-control mt-5 cursor-pointer",
                                            "value": function () {
                                                let title = "";
                                                $saver.custom_types.map(i => (( i.id === $saver.setting_page.type ? title = i.name : "")));
                                                return (title.length === 0 ? ufo.lng($saver.setting_page.type) : title)
                                            }(),
                                            "placeholder": ufo.lng("Select"),
                                            "readonly": true
                                        }
                                    }
                                ]
                            },
                            {
                                "tag": "span",
                                "attrs": {"class": "mr-5 ml-5 mt-10 db ufo-select-status"},
                                "html": [
                                    {
                                        "tag": "span",
                                        "html": ufo.lng("status"),
                                        "attrs": {
                                            "class": "font-size-15px db"
                                        }
                                    },
                                    {
                                        "tag": "input",
                                        "attrs": {
                                            "class": "form-control mt-5 cursor-pointer",
                                            "value": ufo.lng($StatusPage[$saver.setting_page.status]),
                                            "placeholder": ufo.lng("Select status"),
                                            "readonly": true
                                        }
                                    }
                                ]
                            },
                            {
                                "tag": "span",
                                "attrs": {"class": "mr-5 ml-5 mt-10 db ufo-tags-page"},
                                "html": [
                                    {
                                        "tag": "span",
                                        "html": ufo.lng("Tags"),
                                        "attrs": {
                                            "class": "font-size-15px db"
                                        }
                                    },
                                    {
                                        "tag": "input",
                                        "attrs": {
                                            "class": "form-control mt-5",
                                            "value": $saver.setting_page.tags,
                                            "placeholder": ufo.lng("Tags")
                                        }
                                    },
                                    {
                                        "tag": "span",
                                        "html": ufo.lng("Use (,) to separate the tags"),
                                        "attrs": {
                                            "class": "font-size-12px mt-5 db"
                                        }
                                    }
                                ]
                            },
                            {
                                "tag": "span",
                                "attrs": {"class": "mr-5 ml-5 mt-10 db ufo-select-category"},
                                "html": [
                                    {
                                        "tag": "span",
                                        "html": ufo.lng("Category"),
                                        "attrs": {
                                            "class": "font-size-15px db"
                                        }
                                    },
                                    {
                                        "tag": "input",
                                        "attrs": {
                                            "class": "form-control mt-5 cursor-pointer",
                                            "value": function () {
                                                let category = "";
                                                if ( Array.isArray($saver.setting_page.category_restore) ) {
                                                    $saver.setting_page.category_restore.map(i => {
                                                        category += i + ", ";
                                                    });
                                                }
                                                return category.slice(0, -2);
                                            }(),
                                            "placeholder": ufo.lng("Select category"),
                                            "readonly": true
                                        }
                                    }
                                ]
                            },
                        ],
                        "attrs": {
                            "class": "ufo-side-tab-content " + (active ? "active" : ""),
                            "data-tab": "setting"
                        }
                    },
                    "edit-widget": {
                        "tag": "div",
                        "html": html,
                        "attrs": {
                            "class": "ufo-side-tab-content " + (active ? "active" : ""),
                            "data-tab": "edit-widget"
                        }
                    },
                    "widget-setting": {
                        "tag": "div",
                        "html": html,
                        "attrs": {
                            "class": "ufo-side-tab-content " + (active ? "active" : ""),
                            "data-tab": "widget-setting"
                        }
                    }
                };
            },
            AdvanceData ( ) {
                /**
                 * Publish
                 */
                $(`.ufo-publish-page`).unbind().click(function () {
                    const publish = $(this);
                    const oldText = publish.html();

                    if ( ufo.isNULL($saver.setting_page.title) ) {
                        $.ufo_dialog({
                            content: ufo.lng("Please enter a page title")
                        });
                        return false;
                    }
                    if ( ufo.isNULL($saver.setting_page.link) ) {
                        $.ufo_dialog({
                            content: ufo.lng("Please enter a page link")
                        });
                        return false;
                    }

                    const data = {
                        callback: "page_editor",
                        action: "save",
                        link: $saver.setting_page.link,
                        title: $saver.setting_page.title,
                        short_desc: $saver.setting_page.desc,
                        photo: ufo.isNULL($saver.setting_page.photo) ? "[]" : $saver.setting_page.photo,
                        type: $saver.setting_page.type,
                        status: $saver.setting_page.status,
                        password: $saver.setting_page.pass,
                        tags: $saver.setting_page.tags,
                        category: ufo.isNULL($saver.setting_page.category) ? "[]" : $saver.setting_page.category,
                        content: $(`.ufo-content-editor`).html(),
                        setting: ufo.isNULL($saver.widget_saver) ? "{}" : JSON.stringify($saver.widget_saver),
                        script: $saver.codes
                    };

                    if ( !ufo.isNULL(ufo.GET("page")) ) {
                        data["page"]   = Number(atob(ufo.GET("page")));
                        data["action"] = "update";
                    }

                    $.fun().do({
                        name: "req",
                        param: {
                            data: data,
                            dataType: "json",
                            loader ( ) {
                                publish.html(ufo.lng("Wait"));
                                publish.attr("disabled", true);
                            },
                            done ( result ) {
                                publish.html(oldText);
                                publish.attr("disabled", false);
                                $.ufo_dialog({content: ufo.lng(result.message)});
                                if ( result.status === 200 && ufo.isNULL(ufo.GET("page")) ) {
                                    ufo.url.addParam("page", result.id);
                                    location.reload()
                                }
                            },
                            error ( ) {
                                publish.html(oldText);
                                publish.attr("disabled", false);
                                $.ufo_dialog({
                                    content: ufo.lng("Connection error")
                                });
                            }
                        }
                    });
                });

                /**
                 * Restore Images
                 */
                $saver.setting_page.photo.map(i => {
                    $(".ufo-photo-box-page").append($self.json2html({
                        "tag": "div",
                        "attrs": {"class": "ufo-img-items"},
                        "html": [
                            {
                                "tag": "img",
                                "attrs": {
                                    "src": i
                                }
                            }
                        ]
                    }));
                });
                function img_remove () {
                    const img = $(this);
                    const src = img.find("img").attr("src");
                    $saver.setting_page.photo = $saver.setting_page.photo.filter(v => v !== src);
                    img.remove();
                }
                $(".ufo-img-items").unbind().click(img_remove);

                /**
                 * Select Images
                 */
                $(`.ufo-select-img-items`).unbind().click(function () {
                    const select     = $(this);
                    const select_box = $(".ufo-photo-box-page");
                    $.fun().do({
                        name: "media",
                        param: {
                            id: "ufo-photo-page",
                            reset: false,
                            show_label: true,
                            limit: 99,
                            types: "img",
                            loader ( ) {
                                select.addClass("loading");
                            },
                            done ( ) {
                                select.removeClass("loading");
                            },
                            result ( result ) {
                                result.map(i => {
                                    if ( !$saver.setting_page.photo.includes(i) ) {
                                        select_box.append($self.json2html({
                                            "tag": "div",
                                            "attrs": {"class": "ufo-img-items"},
                                            "html": [
                                                {
                                                    "tag": "img",
                                                    "attrs": {
                                                        "src": i
                                                    }
                                                }
                                            ]
                                        }));
                                        $saver.setting_page.photo.push(i);
                                    }
                                });
                                $(".ufo-img-items").unbind().click(img_remove);
                            }
                        }
                    });
                });

                /**
                 * Link
                 */
                $(`input.ufo-page-link`).unbind().bind("input", function () {
                    $(this).val($(this).val().replaceAll(" ", "-"));
                    $saver.setting_page.link = $(this).val();
                }).val($saver.setting_page.link);

                /**
                 * Title
                 */
                if ( $saver.setting_page.title.toString().length === 0 ) {
                    $(`.ufo-header-editor .title`).html(ufo.lng("Untitled"));
                } else {
                    $(`.ufo-header-editor .title`).html($saver.setting_page.title);
                }
                $(`input.ufo-page-title`).unbind().bind("input", function () {
                    $saver.setting_page.title = $(this).val();
                    $saver.setting_page.link = $saver.setting_page.title.replaceAll(" ", "-");

                    $(`.ufo-header-editor .title`).html($saver.setting_page.title);
                    $(`input.ufo-page-link`).val($saver.setting_page.link)
                });

                /**
                 * Short description
                 */
                $(`.ufo-short-description`).unbind().bind("input", function () {
                    $saver.setting_page.desc = $(this).val();
                });

                /**
                 * Select Type
                 */
                $(`.ufo-select-type`).unbind().click(function () {
                    const $select = $(this);
                    $.ufo_dialog({
                        title: ufo.lng("Select"),
                        content: [{
                            name: ufo.lng("page"),
                            id: "page"
                        }, {
                            name: ufo.lng("article"),
                            id: "article"
                        }, ...$saver.custom_types],
                        options: {
                            selection: true,
                            textField: 'name',
                            valueField: 'id',
                            callbacks: {
                                itemSelect: function (e, i) {
                                    $saver.setting_page.type = i.id;
                                    $select.find("input").val(i.name);
                                }
                            }
                        }
                    });
                });

                /**
                 * Select Status
                 */
                $(`.ufo-select-status`).unbind().click(function () {
                    const $select = $(this);
                    $.ufo_dialog({
                        title: ufo.lng("Select status"),
                        content: function () {
                            let array = [], c = 0;
                            $StatusPage.map(i=>{
                                array.push({name: i, id: c});c++
                            });
                            return array;
                        }(),
                        options: {
                            selection: true,
                            textField: 'name',
                            valueField: 'id',
                            callbacks: {
                                itemSelect: function (e, i) {
                                    $(`.ufo-select-pass-page`).remove();
                                    $saver.setting_page.pass = "";

                                    $select.find("input").val(i.name);
                                    $saver.setting_page.status = Number(i.id);

                                    if ( $saver.setting_page.status === 3 ) {setPassword($select)}
                                }
                            }
                        }
                    });
                });
                function setPassword ( after ) {
                    const label = $(`<label class="mr-5 ml-5 mt-10 db ufo-select-pass-page">${ufo.lng("password")}</label>`);
                    const input = $(`<input>`);
                    input.addClass("ufo-password-page");
                    input.addClass("form-control");
                    label.append(input); after.after(label);
                    input.bind("input", function () {$saver.setting_page.pass = $(this).val()}).val($saver.setting_page.pass);
                }
                if ( $saver.setting_page.status === 3 ) {
                    setPassword($(`.ufo-select-status`));
                }

                /**
                 * Select Tags
                 */
                $(`.ufo-tags-page input`).unbind().bind("input", function () {
                    $saver.setting_page.tags = $(this).val();
                });

                /**
                 * Select Category
                 */
                $(`.ufo-select-category`).unbind().click(function () {
                    const $select = $(this), $text = $select.find("input").val();

                    $.fun().do({
                        name: "req",
                        param: {
                            data: {
                                callback: "get_all_category",
                                type: $saver.setting_page.type === "article" ? "page" : $saver.setting_page.type,
                                editor: true
                            },
                            dataType: "json",
                            loader ( ) {
                                $select.find("input").val(ufo.lng("Loading"));
                            },
                            done ( result ) {
                                $select.find("input").val($text);
                                $.ufo_dialog({
                                    title: ufo.lng("Select category"),
                                    content: function () {
                                        let array = [];
                                        $.each(result, (k, v) => {
                                            array.push({name: v, id: k});
                                        });
                                        return array;
                                    }(),
                                    options: {
                                        selection: true,
                                        textField: 'name',
                                        valueField: 'id',
                                        multiple: true,
                                        allowSearch: true,
                                        okText: ufo.lng("select"),
                                        callbacks: {
                                            itemSelect: function (e, i) {
                                                let a = []; i.map(o => a.push(o.id));
                                                $saver.setting_page.category = a;
                                                $select.find("input").val("");
                                                i.map(i => {
                                                    $select.find("input").val($select.find("input").val() + i.name + ", ")
                                                });
                                                $select.find("input").val($select.find("input").val().slice(0, -2));
                                            }
                                        }
                                    },
                                    done: function () {
                                        $(`.dlg-search`).attr("placeholder", ufo.lng("Search"))
                                    }
                                });
                            },
                            error ( ) {
                                $.ufo_dialog({
                                    content: ufo.lng("Error loading information")
                                });
                            }
                        }
                    });
                });
            },

            get_data_widgets ( ) {
                $.fun().do({
                    name: "req",
                    param: {
                        url: ufo_info.widgets,
                        dataType: "json",
                        done ( result ) {
                            loader(true);

                            $self.set_data_widgets(result.widgets);
                            $self.set_data_shortcodes(result.shortcodes);

                            $self.renderSideTabs();
                            $self.accordion();
                            $self.side();
                        },
                        error ( xhr ) {
                            alert(ufo.lng("Connection error"));
                        }
                    }
                });
            },
            set_data_widgets ( widgets ) {
                $widgets = widgets;
            },
            set_data_shortcodes ( shortcodes ) {
                $shortcodes = shortcodes;
            },
            render_item_widgets ( ) {
                const items = [];
                $.each($widgets, (k, v) => {
                    items.push({
                        "tag": "div",
                        "html": [
                            {
                                "tag": "div",
                                "html": [
                                    $self.iconWidget(v.icon),
                                    {
                                        "tag": "strong",
                                        "html": [v.title],
                                        "attrs": {
                                            "class": "title"
                                        }
                                    }
                                ],
                                "attrs": {
                                    "class": "ufo-widget",
                                    "data-widget": v.name
                                }
                            }
                        ],
                        "attrs": {
                            "class": "ufo-widget-column"
                        }
                    })
                });
                return items;
            },
            iconWidget ( icon ) {
                if ( $self.isLink(icon) ) {
                    return {
                        "tag": "img",
                        "attrs": {
                            "src": icon
                        }
                    };
                } else {
                    return {
                        "tag": "i",
                        "attrs": {
                            "class": icon
                        }
                    };
                }
            },

            json2html ( json ) {
                return $.fun().do({
                    name: "json2html",
                    param: json
                });
            },
            html2json ( html ) {
                return $.fun().do({
                    name: "html2json",
                    param: html
                });
            },

            response ( ) {
                const headTitle = $(`header .right .title`);

                headTitle.css({width: 0});
                headTitle.css({
                    width: $(`header .right`).width() + "px"
                });
            },
            config ( ) {
                $self.save_history();

                $self.execCommand("defaultParagraphSeparator", "div");
                $self.pasteEvent();

                $Rich.bind("input", function () {
                    $(this).find("*").each(function () {
                        let dir = $(this).css("text-align");

                        if ( dir == "right" ) {
                            dir = "rtl";
                        }
                        if ( dir == "left" ) {
                            dir = "ltr";
                        }
                        if ( dir == "center" ) {
                            dir = "";
                        }

                        $(this).css({
                            direction: dir
                        });
                    });
                    $self.emptyAction();
                    $self.save_history();
                });
                $Rich.bind('keydown', 'ctrl+z', function ( e ) {
                    e.preventDefault();
                    $self.history_undo();
                });
                $Rich.bind('keydown', 'ctrl+y', function ( e ) {
                    e.preventDefault();
                    $self.history_redo();
                });
                $Rich.bind('keydown', 'ctrl+shift+z', function ( e ) {
                    e.preventDefault();
                    $self.history_redo();
                });
                $(window).bind('keydown', 'ctrl+z', function ( e ) {
                    e.preventDefault();
                    $self.history_undo();
                });
                $(window).bind('keydown', 'ctrl+y', function ( e ) {
                    e.preventDefault();
                    $self.history_redo();
                });
                $(window).bind('keydown', 'ctrl+shift+z', function ( e ) {
                    e.preventDefault();
                    $self.history_redo();
                });
                $Rich.bind('keydown', 'ctrl+left', function ( e ) {
                    e.preventDefault();
                    $self.runCMD("justifyLeft");
                });
                $Rich.bind('keydown', 'ctrl+right', function ( e ) {
                    e.preventDefault();
                    $self.runCMD("justifyRight");
                });
                $Rich.bind('keydown', 'ctrl+space', function ( e ) {
                    e.preventDefault();
                    $self.runCMD("justifyCenter");
                });

                $.fun().apply({
                    name: "ufo_editor_do_draggable",
                    method: function () {
                        $self.draggable();
                    }
                });
                $.fun().apply({
                    name: "ufo_end_edit_widget",
                    method: $self.EndEditWidgets
                });
                $.fun().apply({
                    name: "ufo_save_history",
                    method: $self.save_history
                });
                $.fun().apply({
                    name: "ufo_save_data_widget",
                    method: function ({name, data}) {
                        $saver.widget_saver[name] = data;
                    }
                });
                $.fun().apply({
                    name: "ufo_get_data_widget",
                    method: function (name) {
                        return (typeof $saver.widget_saver[name] !== "undefined" ? $saver.widget_saver[name] : null);
                    }
                });

                $Rich.droppable({
                    over ( e, ui ) {
                        if ( $(ui.helper).hasClass("ufo-widget") || $(ui.helper).hasClass("ufo-shortcodes-widget") ) {
                            $saver.drop_over = true;
                        }
                    },
                    out ( e, ui ) {
                        $saver.drop_over = false;
                    },
                    drop: function (e, ui) {
                        $saver.drop_over = false;
                        if ( $(ui.draggable).hasClass("ufo-shortcodes-widget") ) {
                            $self.addRangeDrag(e);
                            $self.execCommand("insertHtml", $saver.now_content_dragged);
                        }
                    }
                });
                $self.droppable_widgets();
                $self.sortable($Rich);
            },
            emptyAction ( ) {
                if ( $Rich.find("*").length === 0 ) {
                    $Rich.empty();
                    $self.execCommand("insertHTML", `<div class='ufo-sortable ufo-rich-text' style='direction: ${ufo.dir}'><br></div>`);
                }
            },

            save_history ( force ) {
                $Rich.find("*").removeClass("ufo-hover"); $(".ufo-widget-options").remove();

                if ( canvas_history.length > 25 ) {
                    canvas_history = canvas_history.slice(5);
                }

                if (cur_history_index < canvas_history.length - 1) {
                    canvas_history = canvas_history.slice(0, cur_history_index + 1);
                    cur_history_index++;
                }
                let cur_canvas = JSON.stringify($(".ufo-content-editor").html());
                if (cur_canvas != canvas_history[cur_history_index] || force == 1) {
                    canvas_history.push(cur_canvas);
                    cur_history_index = canvas_history.length - 1;
                }

                $self.addEventWidgets();
            },
            history_undo ( ) {
                if (cur_history_index > 0) {
                    s_history = false;
                    let canv_data = JSON.parse(canvas_history[cur_history_index - 1]);
                    $(".ufo-content-editor").html(canv_data);
                    cur_history_index--;
                }
                $self.fixProblemHistory();
            },
            history_redo ( ) {
                if (canvas_history[cur_history_index + 1]) {
                    let s_history = false;
                    let canv_data = JSON.parse(canvas_history[cur_history_index + 1]);
                    $(".ufo-content-editor").html(canv_data);
                    cur_history_index++;
                }
                $self.fixProblemHistory();
            },
            fixProblemHistory ( ) {
                /**
                 * Replace Fake Selection
                 */
                $(`.ufo-fake-select`).each(function () {
                    $(this).replaceWith($(this).html());
                });

                /**
                 * Add EventWidgets
                 */
                $self.addEventWidgets();

                /**
                 * Remove Properties
                 */
                $Rich.find("*").removeClass("ufo-hover");
                $(".ufo-widget-options").remove();

                /**
                 * Sort And Drop In Container
                 */
                $self.sortable($(".ufo-sortable-container"));
                $self.dropToWidget(".ufo-droppable-container");

                /**
                 * Redo Undo Close Edit Widget
                 */
                $.fun().do({name: "ufo_end_edit_widget"});
            },

            setupToolbar ( ) {
                const do_func = [];
                $(`.ufo-toolbar-wrp`).empty();
                $.each($Toolbar, ( k, v ) => {
                    const column = $(`<div>`);
                    column.addClass("ufo-toolbar-column");

                    $.each(v, ( key, v ) => {
                        let cmd = $(`<button>`);

                        if ( typeof v.type !== "undefined" ) cmd = $(`<${v.type}>`);

                        switch (v.type) {
                            case "select":break;
                            default: cmd.addClass(v.icon);
                        }

                        if ( typeof v.class !== "undefined" ) cmd.attr("class", v.class);

                        cmd.attr("data-cmd", key);
                        cmd.attr("data-def", true);

                        if ( typeof v.default !== "undefined" ) {
                            cmd.attr("data-def", v.default);
                        }
                        if ( typeof v.onclick !== "undefined" ) {
                            cmd.click(v.onclick);
                        }

                        column.append(cmd);

                        if ( typeof v.function !== "undefined" ) {
                            do_func.push(v.function);
                        }
                    });

                    $(`.ufo-toolbar-wrp`).append(column);
                });
                $.each(do_func, ( k, v ) => v());
                setTimeout($self.setupToolbarCmd, 150);
            },
            setupToolbarCmd ( ) {
                $(".ufo-header-editor").ufo_scroll();

                $(`.ufo-toolbar-column button`).click(function () {
                    if ( $(this).data("def") ) {
                        const cmd = $(this).data("cmd");
                        $self.execCommand(cmd);
                        $Rich.focus();
                    }
                });

                const picker = $self.colorPicker(`.ufo-toolbar-column button[data-cmd="color"]`);
                picker.on('save', (color) => {
                    color = color.toHEXA().toString();
                    $self.setColor(color);
                });
            },
            accordion ( target = ".ufo-editor-accordion" ) {
                try {
                    document.querySelectorAll(target).forEach((accordion) => {
                        accordion.onclick = function () {
                            this.classList.toggle("open");

                            let content = this.nextElementSibling;

                            if (content.style.maxHeight) {
                                content.style.maxHeight = null;
                            } else {
                                content.style.maxHeight = content.scrollHeight + "px";
                            }
                        };
                    });
                    if ( !ufo.os.mobile ) {
                        $(target + ".active").click();
                    }
                } catch (e) {
                    $.console().group("Accordion Error");
                    $.console().print(e);
                    $.console().groupEnd("Accordion Error");
                }
            },
            side ( ) {
                $(`.search-shortcodes`).bind("input", function () {
                    const value = $(this).val().toLowerCase();
                    $(".ufo-shortcodes-widget").filter(function() {
                        $(this).toggle($(this).find("span").text().toLowerCase().indexOf(value) > -1);
                    });
                });
                $(`.search-widgets`).bind("input", function () {
                    const value = $(this).val().toLowerCase();
                    $(".ufo-widget").filter(function() {
                        $(this).toggle($(this).find("strong").text().toLowerCase().indexOf(value) > -1);
                    });
                });
            },

            execCommand ( c, v = "" ) {
                document.execCommand(c,false, v);
                $Rich.focus();
                $self.save_history();
            },
            commandState ( cmd ) {
                let is = false;
                if (document.queryCommandState) is = document.queryCommandState(cmd);
                return is;
            },
            runCMD ( target ) {
                $(`[data-cmd="${target}"]`).click();
            },
            hasStateActive ( btn, cmd ) {
                btn.click(function () {
                    if ( cmd.indexOf("justify") >-1 ) {
                        $self.removeActive([
                            "justifyRight",
                            "justifyCenter",
                            "justifyLeft"
                        ]);
                    }
                    if ( $(this).hasClass("active") ) {
                        $(this).removeClass("active");
                    } else {
                        if ( window.getSelection().toString().length > 0 && !$self.commandState(cmd) ) {
                            btn.addClass("active");
                        } else {
                            btn.removeClass("active");
                        }
                    }
                });
                $Rich.on("focus selectstart onclick keyup keydown mouseenter mouseup touchstart touchmove touchend", $Rich, function(){
                    if ($self.commandState(cmd)) {
                        btn.addClass("active");
                    } else {
                        btn.removeClass("active");
                    }
                });
            },
            pasteEvent ( ) {
                $Rich[0].addEventListener('paste', (e) => {
                    e.preventDefault();
                    const value = e.clipboardData.getData("text/plain");
                    document.execCommand('insertHTML', false, value);
                });
            },
            removeActive ( array ) {
                array.map(i => {
                    $(`.ufo-toolbar-column button[data-cmd="${i}"]`).removeClass("active");
                });
            },

            setColor (color) {
                document.execCommand('styleWithCSS', false, true);
                document.execCommand('foreColor', false, color);
                $Rich.focus();
            },
            colorPicker ( el, option ) {
                return Pickr.create({
                    el: el,
                    theme: "nano",
                    default: '#000',
                    swatches: [
                        '#000',
                        '#ff0000',
                        '#007eff',
                        '#0034ff',
                        '#00ff1a',
                        '#f600ff',
                        '#f800ff'
                    ],
                    preview: true,
                    opacity: false,
                    hue: false,
                    autoReposition: true,
                    defaultRepresentation: 'HEXA',
                    components: {
                        preview: true,
                        opacity: true,
                        hue: true,
                        interaction: {
                            input: true,
                            save: true
                        }
                    },
                    i18n: $colorPickerI18n,
                    ...option
                });
            },

            draggable ( ) {
                $self.shortcodes_drag();
                $self.widgets_drag();
            },
            sortable ( container, options = {} ) {
                let clone, before, parent;

                container.sortable({
                    placeholder: "ufo-sort-mark",
                    helper: "clone",
                    cursor: "move",
                    handle: ".ufo-move-widget", cancel: "",
                    start: function (event, ui) {
                        $(ui.item).show();
                        clone = $(ui.item).clone();
                        before = $(ui.item).prev();
                        parent = $(ui.item).parent();

                        $(ui.helper).removeAttr("class");
                        $(ui.helper).removeAttr("dir");
                        $(ui.helper).html(`<div class="ufo-widget"><i class="ufo-icon-move"></i><strong class="title">${ufo.lng("Movement")}</strong></div>`);

                        $(ui.helper).addClass("ufo-widget-container");
                        $(ui.helper).css({
                            "width": "100px",
                            "height": "100px",
                            "background": "white",
                            "box-shadow": "rgba(0, 0, 0, 0.05) 0px 1px 2px 0px",
                            "border-radius": "6px",
                            "cursor": "grabbing"
                        });
                        $(ui.item).hide();
                    },
                    stop ( ) {
                        $self.save_history();
                    },
                    receive: function (event, ui) {
                        $self.save_history();
                        container.find("*").click(function() { $(this).focus(); });
                    }, ...options
                }).mousedown(function(event) {
                    container.sortable("option", "cursorAt", {left: 0, top: 0});
                });
            },
            resetDragSaver ( ) {
                $saver.now_content_dragged  = "";
                $saver.now_info_widget.type = null;
            },
            addRangeDrag ( e ) {
                try {
                    let sel = document.getSelection();
                    if (document.caretRangeFromPoint) {
                        let range = document.caretRangeFromPoint(e.clientX, e.clientY);
                        sel.removeAllRanges();
                        sel.addRange(range);
                    } else if (e.rangeParent) {
                        let range = document.createRange();
                        range.setStart(e.rangeParent, e.rangeOffset);
                        sel.removeAllRanges();
                        sel.addRange(range);
                    } else if (sel.rangeCount == 0) {
                        let range = document.createRange();
                        sel.addRange(range);
                    }
                } catch (e) {}
            },
            shortcodes_drag ( ) {
                if ( !ufo.os.mobile ) {
                    $(".ufo-shortcodes-widget").draggable({
                        cursor: "text",
                        handle: ".ufo-drag-handle-shortcode",
                        helper: function (e) {
                            eventDragged.trigger("drag-start");
                            const item = $(this).clone();
                            item.css({width: "200px"});
                            item.find("div").css({
                                boxShadow: "rgba(3, 102, 214, 0.3) 0px 0px 0px 3px",
                                opacity: 0.9
                            });
                            item.addClass("dragged");
                            item.find(".ufo-drag-handle-shortcode").remove();
                            $saver.now_content_dragged = `${item.data("shortcode")}`;
                            return item;
                        },
                        scroll: false,
                        stop  : $self.resetDragSaver,
                        start (e, ui) {
                            $(`body`).append(ui.helper);
                            eventDragged.trigger("drag-start", ui.helper);
                        }
                    });
                } else {
                    $(".ufo-shortcodes-widget").click(function () {
                        const shortcode = `[${$(this).find("span").text()}]`;
                        shortcode.copy();
                        $.ufo_dialog({
                            title: `<span class="font-size-16px">${ufo.lng("Copied")}</span>`,
                            content: shortcode,
                            options: {
                                okText: ufo.lng("close")
                            }
                        });
                    });
                }
            },
            widgets_drag ( ) {
                $(".ufo-widget").draggable({
                    cursor: "grabbing",
                    scroll: false,
                    stop: $self.resetDragSaver,
                    helper: function(e) {
                        const item = $(this).clone();
                        const template = $(`<div>`);
                        template.addClass('ufo-widget-column');
                        template.html($self.json2html($widgets[item.data("widget")].template));
                        item.css({width: "200px"});
                        item.css({boxShadow: "rgba(3, 102, 214, 0.3) 0px 0px 0px 3px", width: "110px", opacity: 0.8});
                        item.addClass("dragged");
                        $saver.now_content_dragged  = template.html();
                        $saver.now_info_widget.type = $widgets[item.data("widget")].type;
                        return item;
                    },
                    start ( e, ui ) {
                        $(`body`).append(ui.helper);
                        eventDragged.trigger("drag-start", ui.helper);
                    }
                });
            },
            droppable_widgets ( ) {
                $ContentDrop.droppable({
                    over ( e, ui ) {
                        if ( $(ui.draggable).hasClass("ufo-widget") ) {
                            $ContentDrop.addClass("drag-over");
                        }
                    },
                    out ( e, ui ) {
                        if ( $(ui.draggable).hasClass("ufo-widget") ) {
                            $ContentDrop.removeClass("drag-over");
                        }
                    },
                    drop: function (e, ui) {
                        $ContentDrop.removeClass("drag-over");
                        if ( $(ui.draggable).hasClass("ufo-widget") ) {
                            let container = $("<div>");

                            container.addClass("ufo-sortable");
                            container.attr("dir", ufo.dir);

                            if ( $saver.now_info_widget.type !== 1 ) {
                                container.addClass("ufo-elements-container");
                                container.attr("contenteditable", false);
                                container.attr("data-widget", $(ui.helper).data("widget"));
                                container.html(`<div class="ufo-child-widget">${$self.json2html($widgets[$(ui.draggable).data("widget")].template)}</div>`);
                            } else {
                                container.addClass("ufo-rich-text");
                                container.html(ufo.lng("Type something..."));
                            }

                            $Rich.append(container);

                            $self.save_history();
                        }
                    }
                });
            },
            dropToWidget ( widget ) {
                $(widget).droppable({
                    accept: ".ufo-widget",
                    connectToSortable: widget,
                    stop: $self.resetDragSaver,
                    over(e, ui) {
                        if ( $(ui.helper).hasClass("ufo-widget") || $(ui.helper).hasClass("ufo-shortcodes-widget") ) {
                            $saver.drop_over = true;
                            $(this).addClass("over");
                        }
                    },
                    out(e, ui) {
                        $saver.drop_over = false;
                        $(this).removeClass("over");
                    },
                    drop(e, ui) {
                        $saver.drop_over = false;

                        const helper = $(ui.helper);
                        $(this).removeClass("over");

                        if ( $saver.now_info_widget.type !== 1 ) {
                            $(this).append(`<div class="ufo-elements-container ufo-sortable" data-widget="${helper.data("widget")}"><div class="ufo-child-widget">${$saver.now_content_dragged}</div></div>`);
                        } else {
                            $(this).append(`<div class="ufo-sortable ufo-rich-text" dir="${ufo.dir}" data-widget="${helper.data("widget")}" contenteditable="true">${ufo.lng("Type something...")}</div>`);
                        }

                        $self.save_history();
                    }
                });
            },

            clearEventWidgets ( ) {
                $("*").removeClass("ufo-hover");
                $(".ufo-widget-options").remove();
            },
            addEventWidgets ( ) {
                let mouseWheel = false, wheelTimeout;

                $Rich.unbind("wheel").bind("wheel", function () {
                    clearTimeout(wheelTimeout);
                    mouseWheel = true;
                    wheelTimeout = setTimeout(()=>{
                        mouseWheel = false;
                        clearTimeout(wheelTimeout);
                    }, 300);
                });

                function eventHover () {
                    if ( mouseWheel ) return false;

                    $Rich.find("*").removeClass("ufo-hover");
                    $(".ufo-widget-options").remove();

                    if ( $(".ui-sortable-helper").length || $saver.drop_over ) return false;

                    const element   = $(this);
                    const direction = element.css("direction");
                    const position  = element.position();
                    const width     = element.width();
                    const height    = element.height();
                    const options   = $(`<div>`);

                    element.addClass("ufo-hover");
                    options.addClass("ufo-widget-options");
                    options.attr("contenteditable", false);

                    options.css({
                        width: width, position: "absolute", top: position.top - 28, left: position.left, background: "transparent", direction: ufo.dir
                    });

                    options.append(`<div class="ufo-widget-row">
                                     <button class="btn btn-primary ufo-remove-widget"><i class="ufo-icon-x"></i></button>
                                     <button class="btn btn-primary ufo-move-widget"><i class="ufo-icon-move"></i></button>
                                     ${(typeof element.data("widget") !== "undefined" && !element.hasClass("ufo-rich-text") ? `<button class="btn btn-primary ufo-edit-widget"><i class="ufo-icon-edit"></i></button>` : ``)}
                                 </div>`);

                    element.prepend(options);
                    element.attr("dir", direction);

                    $(".ufo-remove-widget").unbind().click(function () {
                        element.remove();
                        $self.emptyAction();
                        $self.save_history();
                        $.fun().do({name: "ufo_end_edit_widget"});
                        $self.clearEventWidgets();
                    });
                    $(".ufo-edit-widget").unbind().click(function () {
                        $self.EditWidgets(element, $widgets[element.data("widget")]);
                        setTimeout(()=>{
                            $self.clearEventWidgets();
                            if ( $(".side").hasClass("none") ) {
                                $(".side").removeClass("none");
                            }
                        }, 110);
                    });
                }

                if ( ufo.os.mobile ) {
                    $Rich.find(".ufo-sortable").unbind("hover click").on('click', function () {
                        if ( $(this).hasClass("clicked") ) {
                            $self.clearEventWidgets();
                            $(this).removeClass("clicked");
                        } else {
                            eventHover.bind(this)();
                            $(this).addClass("clicked");
                        }
                    });
                } else {
                    $Rich.find(".ufo-sortable")
                        .unbind("hover click")
                        .hover(eventHover, $self.clearEventWidgets)
                        .click($self.clearEventWidgets);
                }

                $(".main").scroll(function () {
                    $Rich.find("*").removeClass("ufo-hover");
                    $(".ufo-widget-options").remove();
                });
            },
            addColumns ( ) {
                let saveBefore = "";
                function addColumns ( ) {
                    saveBefore = $ContentDrop.html();
                    $ContentDrop.html(`
                                <div class="width-100-cent height-25px" style="position: relative;top: -30px;left: 5px;"><i class="ufo-icon-x-circle font-size-25px close-column-select"></i></div>
                                <div class="flex flex-start" style="position: relative;top: -10px">
                                    <i class="ufo-icon-square ufo-add-column" data-column="1"></i>
                                    <i class="ufo-icon-columns ufo-add-column" data-column="2"></i>
                                    <i class="ufo-icon-column-3 ufo-add-column" data-column="3"></i>
                                </div>
                            `);
                    $(`.close-column-select`).unbind().click(function () {
                        $ContentDrop.html(saveBefore);
                        $(`.ufo-add-columns`).unbind().click(addColumns);
                    });
                    $(`.ufo-add-column`).unbind().click(function () {
                        $self.createColumns($(this).data("column"));
                        $(".close-column-select").click();
                    });
                }
                $(`.ufo-add-columns`).unbind().click(addColumns);
            },
            createColumns ( column ) {
                const column_container = $(`<div>`);

                column_container.addClass("ufo-column-widget");
                column_container.addClass("width-100-cent");
                column_container.addClass("ufo-sortable");
                column_container.attr("data-grid", column);
                column_container.attr("contenteditable", false);
                column_container.attr("style", "direction: " + ufo.dir);
                column_container.attr("data-widget", "UFO-Column");

                for (let i = 0; i < column; i++) {
                    const clm = $(`<div class="ufo-column-child ufo-sortable-container ufo-droppable-container ufo-droppable"></div>`);
                    column_container.append(clm);
                    $self.sortable(clm);
                }

                $Rich.append(column_container);

                $self.save_history();
                $self.dropToWidget(".ufo-droppable");
            },

            EditWidgets ( widget, info ) {
                $self.EndEditWidgets();

                const preventChild = ["ufo-column-widget"];
                const tabs  = $self.sideTabs();
                const $tabs = {};

                $(`.side-tabs, .ufo-side-content-container`).addClass("disable").addClass("dn");

                const ul  = $(`<ul>`);
                const div = $(`<div>`);

                ul.addClass("side-tabs").addClass("temporary");
                div.addClass("ufo-side-content-container").addClass("temporary");

                $(`.side`).append(ul).append(div);

                $.each(tabs["edit_widget"], (k, v) => {
                    const Active = (typeof v.active !== "undefined");
                    $tabs[v.tab] = Active;
                    $(`.side-tabs.temporary`).append(`<li class="${(Active ? "active" : "")}" data-tab="${v.tab}">${v.title}</li>`);
                });

                $.each($tabs, (k, v) => {
                    $(`.ufo-side-content-container.temporary`).append(`<div>${$self.json2html($self.sideContent(v)[k])}</div>`);
                });

                $self.tabsClicker();

                const EditContent = $(`.ufo-side-tab-content[data-tab="edit-widget"]`);

                if ( typeof info !== "undefined" && typeof info.controls !== "undefined" ) {
                    EditContent.html($self.json2html(info.controls));
                }

                EditContent.prepend(`<div class="grid-2${(typeof info !== "undefined" && info.document !== null ? " mb-25" : "")}" dir="ltr">
                                <div class="flex flex-start">
                                    <button class="ml-5 mr-5 mt-5 btn btn-danger ufo-edit-widget-back">
                                        <i class="ufo-icon-chevron-left font-size-20px"></i>
                                    </button>
                                </div>
                                <div class="flex flex-end align-center">${(typeof info !== "undefined" && info.document !== null ? `<a href="${info.document}" target="_blank" class="ml-5 mr-5 mt-5">${ufo.lng("Help page")}</a>` : "")}</div>
                            </div>`);

                $self.EventControls();

                const options = $.fun().do({
                    name: (typeof info !== "undefined" && typeof info.name_script !== "undefined" ? info.name_script : (
                        typeof widget.data("widget") !== "undefined" ? widget.data("widget") : Math.random()
                    )),
                    param: widget
                });

                $(`.ufo-edit-widget-back`).unbind().click(function () {$self.EndEditWidgets();});

                let hasPreventChild = false;
                $.each(preventChild, ( k, v ) => hasPreventChild = widget.hasClass(v));
                if ( widget.find(".ufo-child-widget").length === 0 ) hasPreventChild = true;

                $self.AdvanceWidgetEdit((
                    hasPreventChild ? widget : widget.find(".ufo-child-widget")
                ), options);
            },
            AdvanceWidgetEdit ( widget, $prevent = [] ) {
                const $container        = $(`.ufo-side-content-container.temporary`);
                const $editContainer    = $container.find(`[data-tab="edit-widget"]`);
                const $advanceContainer = $container.find(`[data-tab="widget-setting"]`);
                const prevent           = [];

                if ( typeof $prevent["prevent"] === "undefined" ) $prevent["prevent"] = [];

                $.each($prevent["prevent"], (k, v) => prevent[v] = v);

                if ( typeof prevent["class"] === "undefined" ) {
                    /**
                     * Class
                     */
                    $advanceContainer.html($self.json2html({
                        tag: "div",
                        html: [
                            {
                                tag: "label",
                                html: [
                                    ufo.lng("Class"),
                                    {
                                        tag: "input",
                                        attrs: {
                                            class: "form-control mt-5 ufo-widget-add-class",
                                            dir: "ltr"
                                        }
                                    }
                                ]
                            },
                            {
                                tag: "div",
                                html: function () {
                                    let $class = widget.data("class");
                                    let $list  = [];
                                    let $span  = [];
                                    if ( typeof $class !== "undefined" ) {
                                        $list  = $class.split(" ");
                                    }
                                    $.each($list, (k, v) => {
                                        $span.push({
                                            tag: "span",
                                            html: [v]
                                        });
                                    });
                                    return $span;
                                }(),
                                attrs: {
                                    class: "ufo-input-list-tags mt-10",
                                }
                            }
                        ],
                        attrs: {
                            class: "mt-10"
                        }
                    })); class_tag();
                    function class_tag ( ) {
                        let beforeList = [];

                        $(`.ufo-input-list-tags`).empty();
                        if ( typeof widget.attr("data-class") !== "undefined" ) {
                            beforeList = widget.attr("data-class").split(" ");
                            beforeList.map(i => $(`.ufo-input-list-tags`).append(`<span>${i}</span>`));
                        }

                        $(`.ufo-input-list-tags span:empty`).remove();
                        $(`.ufo-input-list-tags span`).unbind().click(function () {
                            const $list = [];

                            $(this).remove();
                            widget.removeClass($(this).text());

                            $(`.ufo-input-list-tags span`).each(function () {$list.push($(this).html());});
                            widget.attr("data-class", $list.join(" "));

                            class_tag();

                            $self.save_history();
                        });

                        $(`.ufo-widget-add-class`).unbind().bind("input", function () {
                            $(this).removeClass("danger");
                            $(this).val($(this).val().replace(/\s+/g, ''));
                        }).keypress(function (e) {
                            const input = $(this);
                            const value = input.val();

                            if (e.which == 13) {
                                if ( value.length !== 0 ) {
                                    if ( !widget.hasClass(value) ) {
                                        $(`.ufo-input-list-tags`).append(`<span>${value}</span>`);

                                        widget.addClass(value);
                                        beforeList.push(value);

                                        widget.attr("data-class", beforeList.join(" "));

                                        input.val(""); class_tag();

                                        $self.save_history();
                                    } else {
                                        input.addClass("danger");
                                        setTimeout(()=>{input.removeClass("danger");}, 1000);
                                    }
                                } else {
                                    input.addClass("danger");
                                    setTimeout(()=>{input.removeClass("danger");}, 1000);
                                }
                                return false;
                            }
                        });
                    }
                }

                if ( typeof prevent["id"] === "undefined" ) {
                    /**
                     * ID
                     */
                    $advanceContainer.append($self.json2html({
                        tag: "div",
                        html: [
                            {
                                tag: "label",
                                html: [
                                    ufo.lng("ID"),
                                    {
                                        tag: "input",
                                        attrs: {
                                            class: "form-control mt-5 ufo-widget-add-id",
                                            dir: "ltr"
                                        }
                                    }
                                ]
                            }
                        ],
                        attrs: {
                            class: "mt-5"
                        }
                    }));
                    const ID = $(".ufo-widget-add-id");
                    ID.val(widget.attr("id"));
                    ID.unbind().bind("input", function () {
                        $(this).val($(this).val().replace(/\s+/g, ''));
                        widget.attr("id", $(this).val());
                        $self.save_history();
                    });
                }

                if ( typeof prevent["background-color"] === "undefined" ) {
                    /**
                     * BackgroundColor
                     */
                    $advanceContainer.append($self.json2html({
                        tag: "div",
                        html: [
                            {
                                tag: "div",
                                html: [
                                    {
                                        tag: "span",
                                        html: [ufo.lng("Background color")]
                                    },
                                    {
                                        tag: "div",
                                        attrs: {
                                            class: "colorbox width-100-cent",
                                        }
                                    }
                                ],
                                attrs: {
                                    "class": "ufo-input-color-wrp ufo-bg-color-selector"
                                }
                            }
                        ],
                        attrs: {
                            class: "mt-20"
                        }
                    }));
                    const bgColor   = $self.colorPicker(`.ufo-bg-color-selector .colorbox`, {
                        default: ( widget.css("background-color") === "rgba(0, 0, 0, 0)" ? "rgb(255, 255, 255)" : widget.css("background-color") )
                    });
                    bgColor.on('save', (color) => {
                        color = color.toHEXA().toString();
                        widget.css("background", color);
                        $self.save_history();
                    });
                }

                if ( typeof prevent["margin"] === "undefined" ) {
                    /**
                     * Margin
                     */
                    $advanceContainer.append($self.json2html({
                        tag: "div",
                        html: [
                            {
                                tag: "span",
                                html: [
                                    {
                                        tag: "span",
                                        html: [ufo.lng("Margin")],
                                        attrs: {class: "mb-10 db"}
                                    },
                                    {
                                        tag: "div",
                                        html: [
                                            {
                                                tag: "div",
                                                html: [{
                                                    tag: "button",
                                                    html: [
                                                        {tag: "i", attrs: {class: "ufo-icon-link font-size-18px"}}
                                                    ], attrs: {class: "active"}
                                                }]
                                            },
                                            {
                                                tag: "div",
                                                html: [{
                                                    tag: "input",
                                                    attrs: {
                                                        dir: "ltr",
                                                        type: "number",
                                                        value: widget.css("margin-top").replace("px", ""),
                                                        "data-type": "top"
                                                    }
                                                }, {
                                                    tag: "span",
                                                    html: [ufo.lng("TOP")],
                                                    attrs: {
                                                        class: "font-size-12px width-100-cent flex flex-center"
                                                    }
                                                }]
                                            },
                                            {
                                                tag: "div",
                                                html: [{
                                                    tag: "input",
                                                    attrs: {
                                                        dir: "ltr",
                                                        type: "number",
                                                        value: widget.css("margin-bottom").replace("px", ""),
                                                        "data-type": "bottom"
                                                    }
                                                },{
                                                    tag: "span",
                                                    html: [ufo.lng("BOTTOM")],
                                                    attrs: {
                                                        class: "font-size-12px width-100-cent flex flex-center"
                                                    }
                                                }]
                                            },
                                            {
                                                tag: "div",
                                                html: [{
                                                    tag: "input",
                                                    attrs: {
                                                        dir: "ltr",
                                                        type: "number",
                                                        value: widget.css("margin-right").replace("px", ""),
                                                        "data-type": "right"
                                                    }
                                                }, {
                                                    tag: "span",
                                                    html: [ufo.lng("RIGHT")],
                                                    attrs: {
                                                        class: "font-size-12px width-100-cent flex flex-center"
                                                    }
                                                }]
                                            },
                                            {
                                                tag: "div",
                                                html: [{
                                                    tag: "input",
                                                    attrs: {
                                                        dir: "ltr",
                                                        type: "number",
                                                        value: widget.css("margin-left").replace("px", ""),
                                                        "data-type": "left"
                                                    }
                                                },{
                                                    tag: "span",
                                                    html: [ufo.lng("LEFT")],
                                                    attrs: {
                                                        class: "font-size-12px width-100-cent flex flex-center"
                                                    }
                                                }]
                                            },
                                        ],
                                        attrs: {
                                            class: "ufo-margin-inputs ufo-control-connected-inputs"
                                        }
                                    }
                                ]
                            }
                        ],
                        attrs: {
                            class: "mt-20"
                        }
                    }));
                    const margin_inputs = $(`.ufo-margin-inputs`);
                    margin_inputs.find("button").unbind().click(function () {
                        $(this).toggleClass("active");
                    });
                    margin_inputs.find("input").unbind().bind("input", function () {
                        const top    = Number(margin_inputs.find(`[data-type="top"]`).val()) + "px";
                        const bottom = Number(margin_inputs.find(`[data-type="bottom"]`).val()) + "px";
                        const right  = Number(margin_inputs.find(`[data-type="right"]`).val()) + "px";
                        const left   = Number(margin_inputs.find(`[data-type="left"]`).val()) + "px";
                        if ( margin_inputs.find("button").hasClass("active") ) {
                            margin_inputs.find("input").val($(this).val());
                        }
                        widget.css({margin: top + " " + right + " " + bottom + " " + left});
                    });
                }

                if ( typeof prevent["padding"] === "undefined" ) {
                    /**
                     * Padding
                     */
                    $advanceContainer.append($self.json2html({
                        tag: "div",
                        html: [
                            {
                                tag: "span",
                                html: [
                                    {
                                        tag: "span",
                                        html: [ufo.lng("Padding")],
                                        attrs: {class: "mb-10 db"}
                                    },
                                    {
                                        tag: "div",
                                        html: [
                                            {
                                                tag: "div",
                                                html: [{
                                                    tag: "button",
                                                    html: [
                                                        {tag: "i", attrs: {class: "ufo-icon-link font-size-18px"}}
                                                    ], attrs: {class: "active"}
                                                }]
                                            },
                                            {
                                                tag: "div",
                                                html: [{
                                                    tag: "input",
                                                    attrs: {
                                                        dir: "ltr",
                                                        type: "number",
                                                        value: widget.css("padding-top").replace("px", ""),
                                                        "data-type": "top"
                                                    }
                                                }, {
                                                    tag: "span",
                                                    html: [ufo.lng("TOP")],
                                                    attrs: {
                                                        class: "font-size-12px width-100-cent flex flex-center"
                                                    }
                                                }]
                                            },
                                            {
                                                tag: "div",
                                                html: [{
                                                    tag: "input",
                                                    attrs: {
                                                        dir: "ltr",
                                                        type: "number",
                                                        value: widget.css("padding-bottom").replace("px", ""),
                                                        "data-type": "bottom"
                                                    }
                                                },{
                                                    tag: "span",
                                                    html: [ufo.lng("BOTTOM")],
                                                    attrs: {
                                                        class: "font-size-12px width-100-cent flex flex-center"
                                                    }
                                                }]
                                            },
                                            {
                                                tag: "div",
                                                html: [{
                                                    tag: "input",
                                                    attrs: {
                                                        dir: "ltr",
                                                        type: "number",
                                                        value: widget.css("padding-right").replace("px", ""),
                                                        "data-type": "right"
                                                    }
                                                }, {
                                                    tag: "span",
                                                    html: [ufo.lng("RIGHT")],
                                                    attrs: {
                                                        class: "font-size-12px width-100-cent flex flex-center"
                                                    }
                                                }]
                                            },
                                            {
                                                tag: "div",
                                                html: [{
                                                    tag: "input",
                                                    attrs: {
                                                        dir: "ltr",
                                                        type: "number",
                                                        value: widget.css("padding-left").replace("px", ""),
                                                        "data-type": "left"
                                                    }
                                                },{
                                                    tag: "span",
                                                    html: [ufo.lng("LEFT")],
                                                    attrs: {
                                                        class: "font-size-12px width-100-cent flex flex-center"
                                                    }
                                                }]
                                            },
                                        ],
                                        attrs: {
                                            class: "ufo-padding-inputs ufo-control-connected-inputs"
                                        }
                                    }
                                ]
                            }
                        ],
                        attrs: {
                            class: "mt-20"
                        }
                    }));
                    const padding_inputs = $(`.ufo-padding-inputs`);
                    padding_inputs.find("button").unbind().click(function () {
                        $(this).toggleClass("active");
                    });
                    padding_inputs.find("input").unbind().bind("input", function () {
                        const top    = Number(padding_inputs.find(`[data-type="top"]`).val()) + "px";
                        const bottom = Number(padding_inputs.find(`[data-type="bottom"]`).val()) + "px";
                        const right  = Number(padding_inputs.find(`[data-type="right"]`).val()) + "px";
                        const left   = Number(padding_inputs.find(`[data-type="left"]`).val()) + "px";
                        if ( padding_inputs.find("button").hasClass("active") ) {
                            padding_inputs.find("input").val($(this).val());
                        }
                        widget.css({padding: top + " " + right + " " + bottom + " " + left});
                    });
                }

                if ( typeof prevent["border-radius"] === "undefined" ) {
                    /**
                     * Border Radius
                     */
                    $advanceContainer.append($self.json2html({
                        tag: "div",
                        html: [
                            {
                                tag: "span",
                                html: [
                                    {
                                        tag: "span",
                                        html: [ufo.lng("Border Radius")],
                                        attrs: {class: "mb-10 db"}
                                    },
                                    {
                                        tag: "div",
                                        html: [
                                            {
                                                tag: "div",
                                                html: [{
                                                    tag: "button",
                                                    html: [
                                                        {tag: "i", attrs: {class: "ufo-icon-link font-size-18px"}}
                                                    ], attrs: {class: "active"}
                                                }]
                                            },
                                            {
                                                tag: "div",
                                                html: [{
                                                    tag: "input",
                                                    attrs: {
                                                        dir: "ltr",
                                                        type: "number",
                                                        value: widget.css("padding-top").replace("px", ""),
                                                        "data-type": "top"
                                                    }
                                                }, {
                                                    tag: "span",
                                                    html: [ufo.lng("TOP")],
                                                    attrs: {
                                                        class: "font-size-12px width-100-cent flex flex-center"
                                                    }
                                                }]
                                            },
                                            {
                                                tag: "div",
                                                html: [{
                                                    tag: "input",
                                                    attrs: {
                                                        dir: "ltr",
                                                        type: "number",
                                                        value: widget.css("padding-bottom").replace("px", ""),
                                                        "data-type": "bottom"
                                                    }
                                                },{
                                                    tag: "span",
                                                    html: [ufo.lng("BOTTOM")],
                                                    attrs: {
                                                        class: "font-size-12px width-100-cent flex flex-center"
                                                    }
                                                }]
                                            },
                                            {
                                                tag: "div",
                                                html: [{
                                                    tag: "input",
                                                    attrs: {
                                                        dir: "ltr",
                                                        type: "number",
                                                        value: widget.css("padding-right").replace("px", ""),
                                                        "data-type": "right"
                                                    }
                                                }, {
                                                    tag: "span",
                                                    html: [ufo.lng("RIGHT")],
                                                    attrs: {
                                                        class: "font-size-12px width-100-cent flex flex-center"
                                                    }
                                                }]
                                            },
                                            {
                                                tag: "div",
                                                html: [{
                                                    tag: "input",
                                                    attrs: {
                                                        dir: "ltr",
                                                        type: "number",
                                                        value: widget.css("padding-left").replace("px", ""),
                                                        "data-type": "left"
                                                    }
                                                },{
                                                    tag: "span",
                                                    html: [ufo.lng("LEFT")],
                                                    attrs: {
                                                        class: "font-size-12px width-100-cent flex flex-center"
                                                    }
                                                }]
                                            },
                                        ],
                                        attrs: {
                                            class: "ufo-border-radius-inputs ufo-control-connected-inputs"
                                        }
                                    }
                                ]
                            }
                        ],
                        attrs: {
                            class: "mt-30"
                        }
                    }));
                    const border_radius = $(`.ufo-border-radius-inputs`);
                    border_radius.find("button").unbind().click(function () {
                        $(this).toggleClass("active");
                    });
                    border_radius.find("input").unbind().bind("input", function () {
                        const top    = Number(border_radius.find(`[data-type="top"]`).val()) + "px";
                        const bottom = Number(border_radius.find(`[data-type="bottom"]`).val()) + "px";
                        const right  = Number(border_radius.find(`[data-type="right"]`).val()) + "px";
                        const left   = Number(border_radius.find(`[data-type="left"]`).val()) + "px";
                        if ( border_radius.find("button").hasClass("active") ) {
                            border_radius.find("input").val($(this).val());
                        }
                        widget.css({
                            '-moz-border-radius': top + " " + right + " " + bottom + " " + left,
                            '-webkit-border-radius': top + " " + right + " " + bottom + " " + left,
                            'border-radius': top + " " + right + " " + bottom + " " + left
                        });
                    });
                }

                if ( typeof prevent["alignment"] === "undefined" ) {
                    /**
                     * Alignment
                     */
                    $advanceContainer.append($self.json2html({
                        tag: "div",
                        html: [
                            {
                                tag: "span",
                                html: [ufo.lng("Alignment")],
                                attrs: {
                                    class: "mt-30 mb-10 db"
                                }
                            },
                            {
                                tag: "div",
                                html: [
                                    {
                                        tag: "button",
                                        html: [{
                                            tag: "i",
                                            attrs: {
                                                class: "ufo-icon-align"
                                            }
                                        }],
                                        attrs: {
                                            class: "ufo-btn-alignment-widget",
                                            "data-align": "inherit"
                                        }
                                    },
                                    {
                                        tag: "button",
                                        html: [{
                                            tag: "i",
                                            attrs: {
                                                class: "ufo-icon-align-right"
                                            }
                                        }],
                                        attrs: {
                                            class: "ufo-btn-alignment-widget",
                                            "data-align": "rtl"
                                        }
                                    },
                                    {
                                        tag: "button",
                                        html: [{
                                            tag: "i",
                                            attrs: {
                                                class: "ufo-icon-align-center"
                                            }
                                        }],
                                        attrs: {
                                            class: "ufo-btn-alignment-widget",
                                            "data-align": "center"
                                        }
                                    },
                                    {
                                        tag: "button",
                                        html: [{
                                            tag: "i",
                                            attrs: {
                                                class: "ufo-icon-align-left"
                                            }
                                        }],
                                        attrs: {
                                            class: "ufo-btn-alignment-widget",
                                            "data-align": "ltr"
                                        }
                                    }
                                ],
                                attrs: {
                                    class: "ufo-set-alignment flex flex-start"
                                }
                            }
                        ],
                        attrs: {
                            class: "mt-20"
                        }
                    }));
                    const alignButtons = $(".ufo-set-alignment");
                    const aligns       = ["rtl", "ltr", "center"];
                    aligns.map(i => {
                        if ( widget.hasClass(`ufo-align-widget-${i}`) ) {
                            alignButtons.find(`button[data-align="${i}"]`).addClass("active");
                        }
                    });
                    alignButtons.find("button").unbind().click(function () {
                        const button = $(this);

                        alignButtons.find("button").removeClass("active");
                        button.addClass("active");

                        aligns.map(i => widget.removeClass(`ufo-align-widget-${i}`));
                        if ( button.data("align") !== "inherit" ) {
                            widget.addClass(`ufo-align-widget-${button.data("align")}`);
                        }
                    });
                }

                if ( typeof prevent["animation"] === "undefined" ) {
                    /**
                     * Animation
                     */
                    $advanceContainer.append($self.json2html({
                        tag: "div",
                        html: [
                            {
                                tag: "span",
                                html: [ufo.lng("Animation")],
                                attrs: {
                                    class: "mt-30 mb-10 db"
                                }
                            },
                            {
                                tag: "div",
                                html: [
                                    {
                                        tag: "select",
                                        html: function () {
                                            const array = [{
                                                tag: "option",
                                                html: ufo.lng("No animation"),
                                                attrs: {
                                                    value: 0
                                                }
                                            }];
                                            $Animations.map(i => {
                                                array.push({
                                                    tag: "option",
                                                    html: i,
                                                    attrs: {
                                                        value: i
                                                    }
                                                });
                                            });
                                            return array;
                                        }(),
                                        attrs: {
                                            class: "form-control ufo-select-animation-widget"
                                        }
                                    },
                                ],
                                attrs: {
                                    class: "ufo-set-alignment flex flex-start"
                                }
                            }
                        ],
                        attrs: {
                            class: "mt-20"
                        }
                    }));
                    const selectAnimate = $(".ufo-select-animation-widget");
                    $Animations.map(i => {
                        if ( widget.hasClass(`ufo-${i}`) ) {selectAnimate.val(i);}
                    });
                    selectAnimate.unbind().bind("input", function () {
                        if ( $(this).val() == 0 ) {
                            $Animations.map(i => {
                                if ( widget.hasClass(`ufo-${i}`) ) {widget.removeClass(`ufo-${i}`);}
                            });
                        } else {
                            $Animations.map(i => {
                                widget.removeClass(`ufo-${i}`);
                            });
                            widget.addClass("ufo-animated");
                            widget.addClass(`ufo-${$(this).val()}`);
                        }
                        $self.save_history();
                    });
                }
            },
            EventControls ( ) {
                $(".ufo-switch-type-wrp").each(function () {
                    const wrp = $("." + $(this).attr("class").replaceAll(" ", "."));
                    wrp.find("button").unbind().click(function () {
                        wrp.find("button").removeClass("active");
                        $(this).addClass("active");
                    });
                });
            },
            EndEditWidgets ( ) {
                $(`[data-tab="setting"]`).removeClass("active");
                $(`.side-tabs.temporary, .ufo-side-content-container.temporary`).remove();
                $(`.side-tabs.disable, .ufo-side-content-container.disable`).removeClass("disable").removeClass("dn");
                $(`[data-tab="widgets"]`).addClass("active");
            },

            codeEditor ( ) {
                if ( !$(`.ufo-code-editor`).length ) {
                    $(`body`).prepend(`<div class="ufo-code-editor"></div>`);
                }

                const $window = $(".ufo-code-editor");
                const $Tabs   = ["php", "javascript", "css"];
                let nowTab    = "";

                $window.html(`<div class="ufo-code-editor-content"></div>`);
                $(".ufo-code-editor-content").html(`<div class="ufo-code-editor-header"></div>`).append(`<div class="ufo-code-editor-pages"></div>`).
                prepend(`<button class="ufo-run-code">${ufo.lng("RUN")}</button>`);

                $Tabs.map(i => $(`.ufo-code-editor-header`).append(`<button data-tab="${i}">${i}</button>`));
                $Tabs.map(i => $(`.ufo-code-editor-pages`).append(`<div class="ufo-code-editor-pc" data-tab="${i}"><textarea></textarea></div>`));

                $(`.ufo-code-editor-header button:first-child`).addClass("active");

                $(`.ufo-run-code`).hide();
                $(`.ufo-code-editor-header button`).unbind().click(function () {
                    $(`.ufo-code-editor-header button`).removeClass("active");
                    $(this).addClass("active");

                    $(`.ufo-code-editor-pages .ufo-code-editor-pc`).removeClass("active");
                    $(`.ufo-code-editor-pages .ufo-code-editor-pc[data-tab="${$(this).data("tab")}"]`).addClass("active");

                    if ( $(this).data("tab") == "php" ) {$(`.ufo-run-code`).hide();} else {$(`.ufo-run-code`).show();}

                    nowTab = $(this).data("tab");
                });

                if ($window.PopupWindow("getState")) $(".ufo-code-editor").PopupWindow("destroy");
                $window.PopupWindow({
                    title: ufo.lng("Code Editor"),
                    modal: false,
                    direction: "vertical",
                    width: 800,
                    height: 500,
                });
                function Windowresponse () {
                    if($(window).width() >= 1024) {
                        $window.PopupWindow("unminimize");
                    } else {
                        $window.PopupWindow("maximize");
                    }
                } Windowresponse();$(window).resize(Windowresponse);
                $(".ufo-code-editor-header").ufo_scroll();

                $(`.ufo-code-editor-pages .ufo-code-editor-pc`).addClass("active");

                let php = $(`[data-tab="php"] textarea`)[0];
                let js  = $(`[data-tab="javascript"] textarea`)[0];
                let css = $(`[data-tab="css"] textarea`)[0];

                try {
                    const $baseIDE = {
                        value: "Type Here",
                        lineNumbers: true,
                        theme: "darcula",
                        autoCloseBrackets: true,
                        autoCloseTags: true,
                        matchBrackets: true,
                        extraKeys: {
                            "Ctrl-Q": function (cm) {cm.foldCode(cm.getCursor());},
                            "Ctrl-Space": "autocomplete"
                        },
                        foldGutter: true,
                        lineWrapping: true,
                        gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"]
                    };

                    $baseIDE["mode"] = "php";
                    php = CodeMirror.fromTextArea(php, $baseIDE);
                    php.getDoc().setValue($saver.codes.php);

                    $baseIDE["mode"] = "javascript";
                    js  = CodeMirror.fromTextArea(js,$baseIDE);
                    js.getDoc().setValue($saver.codes.js);

                    $baseIDE["mode"] = "css";
                    css = CodeMirror.fromTextArea(css, $baseIDE);
                    css.getDoc().setValue($saver.codes.css);

                    php.setSize("100%", "100%");
                    js.setSize("100%", "100%");
                    css.setSize("100%", "100%");

                    php.on("change", function ( ) {
                        $saver.codes.php = php.getDoc().getValue("\n");
                    });
                    js.on("change", function ( ) {
                        $saver.codes.js = js.getDoc().getValue("\n");
                    });
                    css.on("change", function ( ) {
                        $saver.codes.css = css.getDoc().getValue("\n");
                    });

                    $(".ufo-run-code").unbind().click(function () {
                        $.fun().do({name:"ufo_detect_error",param: false});
                        if ( nowTab != "php" ) {
                            switch (nowTab) {
                                case "javascript":
                                    $(`.ufo-temp-script`).remove();
                                    $(`<script class="ufo-temp-script">${$saver.codes.js}</script>`).appendTo(document.body);
                                    break;
                                case "css":
                                    if ( !$(`style.ufo-temp-style`).length ) {
                                        $(`body`).append(`<style class="ufo-temp-style"></style>`);
                                    }
                                    $(`.ufo-temp-style`).html($saver.codes.css);
                                    break;
                                default:break;
                            }
                        }
                        $.console().clear();
                        setTimeout(()=>$.fun().do({name:"ufo_detect_error", param: true}), 1000);
                    });
                } catch (e) {}

                $(`.ufo-code-editor-pages .ufo-code-editor-pc`).removeClass("active");
                $(`.ufo-code-editor-pages .ufo-code-editor-pc:first-child`).addClass("active");

                if ( ufo.os.mobile ) {
                    $(`.popupwindow_titlebar_button_minimize, .popupwindow_titlebar_button_collapse, .popupwindow_titlebar_button_maximize`).remove();
                }
            },

            isLink ( str ) {
                return /(?:https?):\/\/(\w+:?\w*)?(\S+)(:\d+)?(\/|\/([\w#!:.?+=&%!\-\/]))?/.test(str);
            },

            mobile ( ) {
                if ( ufo.os.mobile ) {
                    $(`.side`).addClass("none");
                    eventDragged.bind("drag-start", function ( e, helper ) {
                        $(`.side`).addClass("none");
                    });
                }
            }
        };

        if (ufo.GET("page") != null) {
            methods.restore();
        } else {
            methods.init();
        }
    });
});

/**
 * Widgets
 */
ufo.register(null, function () {
    $.fun().do({
        name: "ufo_editor_init",
        param: function ( ) {

            /**
             * Columns
             */
            $.fun().apply({
                name: "UFO-Column",
                method: function ( widget ) {
                    const $columnChild      = widget.find(".ufo-column-child");
                    const $containerEditor  = $(`.ufo-side-content-container.temporary`);
                    const $editContainer    = $containerEditor.find(`[data-tab="edit-widget"]`);

                    let $nowColumn = 0;

                    /**
                     * Accordion
                     */
                    {
                        let count = 1;

                        $columnChild.each(function () {
                            const c             = $(`<div>`);
                            const accordion     = $(`<button>`);
                            const accordionHtml = $(`<div>`);

                            accordion.addClass("ufo-editor-accordion").addClass("ufo-editor-column-accordion");
                            accordion.data("row", count);
                            accordionHtml.addClass("ufo-editor-accordion-content").addClass("ufo-columns-accordion-content");

                            accordion.html(ufo.lng("Row %n").replace("%n", count));

                            c.append(accordion);
                            c.append(accordionHtml);

                            $editContainer.append(c);

                            count++;
                        });

                        function closeAccordions ( ) {
                            const target = ".ufo-editor-column-accordion.open";
                            document.querySelectorAll(target).forEach(function ( accordion ) {
                                let content = accordion.nextElementSibling;
                                $(target).removeClass("open");
                                $(content).removeAttr("style");
                            });
                        }

                        let accordions = $(".ufo-editor-column-accordion");

                        accordions.unbind().click(function () {
                            const column = widget.find(`.ufo-column-child:nth-child(${$(this).data("row")})`);
                            $(`.ufo-column-child`).removeClass("ufo-hover");
                            closeAccordions();
                            if ( !$(this).hasClass("open") ) {
                                column.addClass("ufo-hover");
                                $nowColumn = parseInt($(this).data("row")) - 1;
                            }
                        });

                        $.fun().do({
                            name: "ufo-editor-accordion",
                            param: ".ufo-editor-column-accordion"
                        });
                    }

                    /**
                     * Add Options
                     */
                    {
                        let accordions = $(".ufo-editor-column-accordion"), count = $nowColumn;

                        $(".ufo-columns-accordion-content").each(function () {
                            const target  = $(this);
                            const options = $("<div>");
                            const column  = $($columnChild[count]);

                            const {width, backgroundColor} = {
                                width: parseFloat(parseFloat(column.css("width")) / parseFloat(widget.css("width")) * 100).toFixed(2),
                                backgroundColor: (column.css("background-color") === "rgba(0, 0, 0, 0)" ? "rgb(255, 255, 255)" : column.css("background-color"))
                            };

                            /**
                             * Size Column
                             */
                            {
                                options.append($.ufo_range({
                                    title: ufo.lng("Width"),
                                    start: parseInt(width) + 1,
                                    min: 20,
                                    change: function ( ) {
                                        const val  = $(this).val();
                                        column.css("width", val + "%");
                                    }
                                }));
                            }

                            /**
                             * Background Color
                             */
                            {
                                const result = $.fun().do({
                                    name: "json2html",
                                    param: {
                                        tag: "div",
                                        html: [
                                            {
                                                tag: "div",
                                                html: [
                                                    {
                                                        tag: "span",
                                                        html: [ufo.lng("Background color")]
                                                    },
                                                    {
                                                        tag: "div",
                                                        attrs: {
                                                            class: "colorbox width-100-cent",
                                                        }
                                                    }
                                                ],
                                                attrs: {
                                                    "class": "ufo-input-color-wrp ufo-bg-column-color-selector"
                                                }
                                            }
                                        ],
                                        attrs: {
                                            class: "mt-20"
                                        }
                                    }
                                });
                                options.append(result);
                            }

                            target.html(options);

                            /**
                             * Events
                             */
                            {
                                /**
                                 * Select Background Color
                                 */
                                const bgColor = $.fun().do({
                                    name: "ufo-editor-color-picker",
                                    param: {
                                        target: ".ufo-bg-column-color-selector .colorbox",
                                        default: backgroundColor
                                    }
                                });
                                bgColor.on('save', (color) => {
                                    color = color.toHEXA().toString();
                                    column.css("background-color", color);
                                });
                            }

                            count++;
                        });
                    }

                    return {
                        prevent: ["alignment", "animation"]
                    };
                }
            });

            /**
             * Button Widget
             */
            $.fun().apply({
                name: "ufo_widget_button",
                method: function (widget) {
                    const $Btn = widget.find("button");
                    const $setting = {
                        textBtn: $(".UFO-Button-input-text-button"),
                        styleBtn: $(".UFO-Button-select-style-button"),
                    };
                    const styles = ["primary", "info", "danger", "success", "light", "dark", "secondary", "warning"];

                    $setting.textBtn.val($Btn.text());
                    $setting.textBtn.unbind("input").bind("input", function () {
                        if ( $(this).val().length !== 0 ) {
                            $Btn.text($(this).val());
                        } else {
                            $Btn.text(ufo.lng("Click"));
                        }
                        $.fun().do({name: "ufo_save_history"});
                    });

                    $setting.styleBtn.unbind("input").bind("input", function () {
                        $.each(styles, (k, v) => $Btn.removeClass("btn-" + v));
                        $Btn.addClass("btn-" + $(this).val());
                        $.fun().do({name: "ufo_save_history"});
                    }).val(function () {
                        let val = styles[0];
                        styles.map(i => {
                            if ( $Btn.hasClass("btn-" + i) ) {
                                val = i
                            }
                        });
                        return val;
                    });
                }
            });

            /**
             * Image Widget
             */
            $.fun().do({
                name: "ufo_save_data_widget",
                param: {
                    name: "UFO-image-widget",
                    data: {}
                }
            });
            $.fun().apply({
                name: "ufo_img_widget",
                method: function (widget) {
                    const image = widget.find("img");
                    const imageSize = {
                        width: image.css("width").replace("px", "").replace("rem", "").replace("%", ""),
                        height: image.css("height").replace("px", "").replace("rem", "").replace("%", "")
                    };
                    const controls  = {
                        width: $(`.ufo-img-width-resizer`),
                        height: $(`.ufo-img-height-resizer`),
                        typeSize: $(`.ufo-switch-size-img button`),
                        imgType: $(".ufo-switch-type-src-img button")
                    };
                    let randomImg = Math.round(Math.random() * 9999999);
                    if ( typeof image.attr("data-img") === "undefined" ) {
                        image.attr("data-img", randomImg);
                    } else {
                        randomImg = image.attr("data-img");
                    }

                    if ( get("size-type") ) controls.typeSize.removeClass("active");
                    if ( get("type-img") ) {
                        controls.imgType.removeClass("active");
                        $(`.ufo-select-image, .ufo-img-src-link`).addClass("dn");
                        if ( get("type-img") === "img" ) {
                            $(`.ufo-select-image`).removeClass("dn");
                        } if ( get("type-img") === "link" ) {
                            $(`.ufo-img-src-link`).removeClass("dn");
                        }
                    }

                    $(`.ufo-switch-size-img button[data-type="${get("size-type")}"]`).addClass("active");
                    $(`.ufo-switch-type-src-img button[data-type="${get("type-img")}"]`).addClass("active");

                    controls.typeSize.click(function () {
                        save("size-type", $(this).data("type"));
                        image.css("width", controls.width.find(`input.value`).val() + $(this).data("type"));
                        image.css("height", controls.height.find(`input.value`).val() + $(this).data("type"));
                    });

                    controls.width.find(`input[type="range"]`).val(imageSize.width);
                    controls.height.find(`input[type="range"]`).val(imageSize.height);

                    controls.width.find(`input.value`).val(imageSize.width);
                    controls.height.find(`input.value`).val(imageSize.height);

                    controls.width.find(`input[type="range"]`).unbind().bind("input", function () {
                        const size = $(this).val();
                        image.css("width", size + $(`.ufo-switch-size-img button.active`).data("type"));
                        controls.width.find(`input.value`).val(size);
                        $.fun().do({name: "ufo_save_history"});
                    });
                    controls.height.find(`input[type="range"]`).unbind().bind("input", function () {
                        const size = $(this).val();
                        image.css("height", size + $(`.ufo-switch-size-img button.active`).data("type"));
                        controls.height.find(`input.value`).val(size);
                        $.fun().do({name: "ufo_save_history"});
                    });

                    controls.width.find(`input.value`).unbind().bind("input", function () {
                        const size = $(this).val();
                        controls.width.find(`input[type="range"]`).val(size);
                        image.css("width", size + $(`.ufo-switch-size-img button.active`).data("type"));
                        $.fun().do({name: "ufo_save_history"});
                    });
                    controls.height.find(`input.value`).unbind().bind("input", function () {
                        const size = $(this).val();
                        controls.height.find(`input[type="range"]`).val(size);
                        image.css("height", size + $(`.ufo-switch-size-img button.active`).data("type"));
                        $.fun().do({name: "ufo_save_history"});
                    });

                    $(".ufo-switch-type-src-img").find("button").click(function () {
                        $(`.ufo-select-image, .ufo-img-src-link`).addClass("dn");
                        save("type-img", $(this).data("type"));
                        if ( $(this).data("type") === "img" ) {
                            $(`.ufo-select-image`).removeClass("dn");
                        } if ( $(this).data("type") === "link" ) {
                            $(`.ufo-img-src-link`).removeClass("dn");
                        }
                    });

                    $(".ufo-select-image img").attr("src", widget.find("img").attr("src"));
                    $(".ufo-select-image").unbind().click(function ( ) {
                        if ( !get("img-selector") ) {
                            save("img-selector", Math.round(Math.random() * 99999));
                        }
                        $.fun().do({
                            name: "media",
                            param: {
                                id: "ufo-img-" + get("img-selector"),
                                reset: false,
                                show_label: true,
                                limit: 1,
                                types: "img",
                                loader ( ) {
                                    $(".ufo-select-image .loader").removeClass("dn");
                                },
                                done ( ) {
                                    $(".ufo-select-image .loader").addClass("dn");
                                },
                                result ( result ) {
                                    set_img(result[0]);
                                    return {
                                        reset: true
                                    };
                                }
                            }
                        });
                    });

                    function set_img ( src ) {
                        $(".ufo-select-image img").attr("src", src);
                        widget.find("img").attr("src", src);
                    }

                    $(".ufo-img-src-link input").unbind().bind("input", function () {
                        set_img($(this).val());
                    }).val(widget.find("img").attr("src"));

                    function save ( name, data ) {
                        let $data = $.fun().do({name: "ufo_get_data_widget", param: "UFO-image-widget"});
                        if ( $data == null ) $data = {};

                        if ( typeof $data[randomImg] === "undefined" ) $data[randomImg] = [];
                        $data[randomImg][name] = data;

                        $.fun().do({
                            name: "ufo_save_data_widget",
                            param: {
                                name: "UFO-image-widget",
                                data: $data
                            }
                        });
                    }
                    function get ( name ) {
                        let $data = $.fun().do({name: "ufo_get_data_widget", param: "UFO-image-widget"});
                        if ( $data == null ) $data = {};
                        if ( typeof $data[randomImg] !== "undefined" ) {
                            return (typeof $data[randomImg][name] !== "undefined" ? $data[randomImg][name] : false);
                        } else {
                            return false;
                        }
                    }
                }
            });

        }
    });
});