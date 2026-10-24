/* ---------- Helpers ---------- */
const $ = s => document.querySelector(s);
const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const money = n => '₱' + Number(n || 0).toLocaleString('en-PH', {maximumFractionDigits: 2});
const uid = p => p + Date.now().toString(36) + Math.random().toString(36).slice(2, 5);
const pad = n => String(n).padStart(2, '0');
const toLocalInput = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
const fmt = d => new Date(d).toLocaleString('en-PH', {month:'short', day:'numeric', year:'numeric', hour:'numeric', minute:'2-digit'});
const DB = {
  get(k, d){ try { const v = JSON.parse(localStorage.getItem(k)); return v ?? d; } catch { return d; } },
  set(k, v){ localStorage.setItem(k, JSON.stringify(v)); }
};
const getUsers = () => DB.get('bm_users', []);
const getBikes = () => DB.get('bm_bikes', []);
const getTx = () => DB.get('bm_tx', []);
const saveUsers = v => DB.set('bm_users', v);
const saveBikes = v => DB.set('bm_bikes', v);
const saveTx = v => DB.set('bm_tx', v);
let session = DB.get('bm_session', null);
const me = () => getUsers().find(u => u.id === session?.id);
const cartKey = () => 'bm_cart_' + session.id;
const getCart = () => DB.get(cartKey(), []);
const saveCart = c => DB.set(cartKey(), c);

function toast(msg, type = 'ok'){
  const t = document.createElement('div');
  t.className = 'toast ' + (type === 'ok' ? '' : type);
  t.textContent = msg;
  $('#toasts').appendChild(t);
  setTimeout(() => { t.style.opacity = '0'; t.style.transition = '.4s'; setTimeout(() => t.remove(), 400); }, 3200);
}

/* ---------- Modal ---------- */
function openModal(html){
  $('#modalRoot').innerHTML = `<div class="backdrop" onmousedown="if(event.target===this)closeModal()"><div class="modal">${html}</div></div>`;
}
function closeModal(){ $('#modalRoot').innerHTML = ''; }
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
function confirmDialog(title, msg, yesLabel, onYes, danger){
  window.__yes = () => { closeModal(); onYes(); };
  openModal(`<button class="x" onclick="closeModal()">✕</button><h2>${title}</h2><p style="color:var(--muted)">${msg}</p>
    <div class="modal-actions"><button class="btn btn-outline" onclick="closeModal()">Cancel</button>
    <button class="btn ${danger ? 'btn-danger' : 'btn-primary'}" onclick="__yes()">${yesLabel}</button></div>`);
}

/* ---------- Bike illustration (SVG) ---------- */
function bikeSVG(type = 'City', color = '#ff6a1a', spin = false){
  const dark = '#0b1d2e';
  let r = 40, cy = 118, tw = 3.5;
  if (type === 'Mountain') tw = 5.5;
  if (type === 'Folding'){ r = 30; cy = 128; }
  if (type === 'BMX'){ r = 34; cy = 124; }
  const wheel = cx => `<g class="wheel ${spin ? 'spin' : ''}">
    <circle cx="${cx}" cy="${cy}" r="${r}" fill="none" stroke="${dark}" stroke-width="${tw}"/>
    <circle cx="${cx}" cy="${cy}" r="${r-6}" fill="none" stroke="#9aa7b3" stroke-width="1"/>
    ${[0,30,60,90,120,150].map(a => `<line x1="${cx}" y1="${cy-r+6}" x2="${cx}" y2="${cy+r-6}" stroke="#9aa7b3" stroke-width="1" transform="rotate(${a} ${cx} ${cy})"/>`).join('')}
    <circle cx="${cx}" cy="${cy}" r="4.5" fill="${color}"/></g>`;
  let bars;
  if (type === 'Road') bars = `M205 ${cy-46} L212 ${cy-60} Q228 ${cy-62} 230 ${cy-46}`;
  else if (type === 'BMX') bars = `M205 ${cy-46} L208 ${cy-72} L232 ${cy-72}`;
  else bars = `M205 ${cy-46} L210 ${cy-62} L228 ${cy-62}`;
  const battery = type === 'Electric'
    ? `<rect x="152.5" y="${cy-25}" width="40" height="10" rx="3" fill="#c6ff3d" stroke="${dark}" stroke-width="1.5" transform="rotate(-31.6 172.5 ${cy-20})"/>` : '';
  const hinge = type === 'Folding' ? `<circle cx="163" cy="${cy-45}" r="5" fill="${dark}"/>` : '';
  return `<svg viewBox="0 0 300 180" xmlns="http://www.w3.org/2000/svg">
    <ellipse cx="152" cy="${cy+r+5}" rx="125" ry="6" fill="rgba(0,0,0,.18)"/>
    ${wheel(70)}${wheel(235)}
    <g stroke="${color}" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" fill="none">
      <line x1="70" y1="${cy}" x2="140" y2="${cy}"/>
      <line x1="70" y1="${cy}" x2="122" y2="${cy-46}"/>
      <line x1="140" y1="${cy}" x2="122" y2="${cy-46}"/>
      <line x1="122" y1="${cy-46}" x2="205" y2="${cy-44}"/>
      <line x1="140" y1="${cy}" x2="205" y2="${cy-40}"/>
      <line x1="205" y1="${cy-40}" x2="235" y2="${cy}"/>
    </g>
    ${battery}${hinge}
    <g stroke="${dark}" stroke-width="4" stroke-linecap="round" fill="none">
      <line x1="122" y1="${cy-46}" x2="122" y2="${cy-54}"/><line x1="109" y1="${cy-54}" x2="136" y2="${cy-54}" stroke-width="6"/>
      <path d="${bars}"/><line x1="205" y1="${cy-46}" x2="205" y2="${cy-40}"/>
      <circle cx="140" cy="${cy}" r="9" stroke-width="3"/><line x1="132" y1="${cy+8}" x2="148" y2="${cy-8}" stroke-width="3"/>
    </g></svg>`;
}
const tint = c => `linear-gradient(135deg, ${c}1f, ${c}55)`;

/* ---------- Seed data ---------- */
function seed(){
  if (!getUsers().length){
    saveUsers([
      {id:'u_admin', name:'BikeMo Admin', email:'admin@bikemo.com', phone:'09000000000', password:'admin123', role:'admin', created:Date.now()},
      {id:'u_demo', name:'Demo Rider', email:'user@bikemo.com', phone:'09171234567', password:'user123', role:'user', created:Date.now()}
    ]);
  }
  if (!getBikes().length){
    const B = (name,type,color,h,d,gears,frame,weight,desc,status='available') =>
      ({id:uid('b_'), name, type, color, ratePerHour:h, ratePerDay:d, gears, frame, weight, desc, status});
    saveBikes([
      B('Trailblazer X1','Mountain','#ff6a1a',80,450,'21-speed','Aluminum','14.5 kg','Rugged suspension bike built for trails, hills and rough roads.'),
      B('Velocity Pro','Road','#e63946',90,500,'18-speed','Carbon','9.8 kg','Ultra-light road bike for speed lovers and long smooth rides.'),
      B('Urban Cruiser','City','#12b5a6',60,350,'7-speed','Steel','13 kg','Comfortable upright bike for everyday city rides and errands.'),
      B('Volt E-Rider','Electric','#7b2ff7',150,900,'Pedal-assist','Aluminum','21 kg','Electric bike with up to 60 km range. Effortless on hills.'),
      B('Pocket Fold','Folding','#ffb703',55,320,'6-speed','Aluminum','11 kg','Folds in seconds. Perfect for commuters and small spaces.'),
      B('Dirt Kid BMX','BMX','#06d6a0',50,300,'Single-speed','Chromoly','10 kg','Tough and playful BMX for tricks, parks and pump tracks.'),
      B('Ridge Runner 29','Mountain','#3a86ff',85,480,'24-speed','Aluminum','14 kg','Big 29-inch wheels roll over anything in your path.'),
      B('Coastal Breeze','City','#f72585',65,380,'3-speed','Steel','14 kg','Retro-style beach cruiser made for relaxed seaside rides.'),
      B('Thunder E-Cargo','Electric','#1d3557',140,850,'Pedal-assist','Alloy','23 kg','Powerful e-bike with strong torque for heavy-duty rides.','maintenance'),
      B('Sprint Aero','Road','#ffd60a',95,520,'22-speed','Carbon','9.2 kg','Aerodynamic racer for personal bests and group rides.','rented')
    ]);
  }
  if (!localStorage.getItem('bm_tx')) saveTx([]);
}

/* ---------- Pricing ---------- */
const UNIT_MAX = {hours:72, days:30, weeks:8};
function durMs(q, u){ return q * (u === 'hours' ? 3600e3 : u === 'days' ? 86400e3 : 7 * 86400e3); }
function unitLabel(q, u){ return `${q} ${q == 1 ? u.slice(0,-1) : u}`; }
function cost(bike, q, u){
  q = Number(q) || 0;
  let base = 0, discount = 0;
  if (u === 'hours') base = q * bike.ratePerHour;
  else if (u === 'days') base = q * bike.ratePerDay;
  else { base = q * 7 * bike.ratePerDay; discount = base * 0.15; }
  return {base, discount, total: base - discount};
}
function costRows(c, u){
  return `<div class="srow"><span>Rental fee</span><span>${money(c.base)}</span></div>
    ${u === 'weeks' ? `<div class="srow"><span>Weekly discount (15%)</span><span>-${money(c.discount)}</span></div>` : ''}
    <div class="srow total"><span>Total</span><b>${money(c.total)}</b></div>`;
}
function dur(ms){
  const m = Math.floor(Math.abs(ms) / 60000), d = Math.floor(m / 1440), h = Math.floor(m % 1440 / 60), mm = m % 60;
  return (d ? d + 'd ' : '') + (h ? h + 'h ' : '') + (!d ? mm + 'm' : '');
}

/* ---------- Views ---------- */
function showView(name){
  document.querySelectorAll('.view').forEach(v => v.classList.add('hidden'));
  $('#view-' + name).classList.remove('hidden');
  window.scrollTo(0, 0);
  if (name === 'landing') renderLanding();
  if (name === 'user') renderUserShell();
  if (name === 'admin') renderAdminShell();
}
function goApp(){
  const u = me();
  if (!u){ session = null; localStorage.removeItem('bm_session'); return showView('landing'); }
  showView(u.role === 'admin' ? 'admin' : 'user');
}
function startRenting(){
  if (me()) goApp(); else { toast('Sign in or create an account to start renting 🚴', 'warn'); openAuth('login', 'user'); }
}

/* ============ LANDING ============ */

function renderLanding() {
  // Dynamic featured bikes only
  const featured = document.querySelector('#featured');

  if (featured) {
    const bikes = getBikes()
      .filter(bike => bike.status === 'available')
      .slice(0, 4);

    featured.innerHTML = bikes
      .map(bike => bikeCard(bike, true))
      .join('');
  }
}


window.addEventListener('scroll', () => $('#nav')?.classList.toggle('solid', window.scrollY > 40));   
function observeReveal(){
  const io = new IntersectionObserver(es => es.forEach(e => {
    if (e.isIntersecting){
      e.target.classList.add('in');
      if (e.target.dataset.count !== undefined) countUp(e.target);
      io.unobserve(e.target);
    }
  }), {threshold:.15});
  document.querySelectorAll('.reveal,[data-count]').forEach(el => io.observe(el));
}
function countUp(el){
  const end = +el.dataset.count; let n = 0;
  const t = setInterval(() => { n += Math.max(1, Math.ceil(end / 30)); if (n >= end){ n = end; clearInterval(t); } el.textContent = n; }, 40);
}

/* ============ AUTH ============ */
function logout(){
  session = null; localStorage.removeItem('bm_session');
  toast('You have been logged out.'); showView('landing');
}

/* ============ CUSTOMER APP ============ */
let userPage = 'browse';
let bf = {q:'', type:'All', sort:'featured', avail:false};
let rf = 'all';
function renderUserShell(){
  const u = me(); if (!u) return goApp();
  $('#userName').textContent = u.name;
  $('#userAv').textContent = u.name[0].toUpperCase();
  renderUser();
}
function userNav(p){ userPage = p; renderUser(); window.scrollTo(0, 0); }
function cartValid(){
  const bikes = getBikes();
  return getCart().filter(i => bikes.find(b => b.id === i.bikeId));
}
function renderUser(){
  const n = cartValid().length;
  $('#userTabs').innerHTML = [['browse','🚲 Browse'],['cart','🛒 Cart'],['rentals','📜 My Rentals']].map(([k, l]) =>
    `<button class="${userPage === k ? 'on' : ''}" onclick="userNav('${k}')">${l}${k === 'cart' && n ? `<span class="cnt">${n}</span>` : ''}</button>`).join('');
  if (userPage === 'browse') renderBrowse();
  else if (userPage === 'cart') renderCart();
  else renderRentals();
}

/* ----- Browse ----- */
function bikeCard(b, landing){
  const can = b.status === 'available';
  return `<div class="bike-card ${landing ? 'reveal' : ''}">
    <div class="bike-art" style="background:${tint(b.color)}"><span class="type">${esc(b.type)}</span><span class="badge ${b.status}">${b.status}</span>${bikeSVG(b.type, b.color)}</div>
    <div class="bike-body"><h3>${esc(b.name)}</h3>
      <div class="specs"><span>⚙️ ${esc(b.gears)}</span><span>🔩 ${esc(b.frame)}</span><span>⚖️ ${esc(b.weight)}</span></div>
      <div class="price"><div><b>${money(b.ratePerHour)}</b><small> / hour</small></div><div style="text-align:right"><b style="font-size:1.4rem;color:var(--navy)">${money(b.ratePerDay)}</b><small> / day</small></div></div>
      ${landing
        ? `<button class="btn btn-primary btn-sm" style="justify-content:center" onclick="startRenting()" ${can ? '' : 'disabled'}>${can ? 'Rent This Bike' : 'Unavailable'}</button>`
        : `<div style="display:flex;gap:8px"><button class="btn btn-outline btn-sm" onclick="openDetails('${b.id}')">Details</button><button class="btn btn-primary btn-sm" style="flex:1;justify-content:center" onclick="openRent('${b.id}')" ${can ? '' : 'disabled'}>${can ? 'Rent Now' : 'Unavailable'}</button></div>`}
    </div></div>`;
}
function renderBrowse(){
  const types = ['All','Mountain','Road','City','Electric','Folding','BMX'];
  $('#userContent').innerHTML = `
    <div class="page-head"><div><h1>CHOOSE YOUR RIDE</h1><p>Browse bikes, check pricing and availability.</p></div></div>
    <div class="filters">
      <input type="text" id="fq" placeholder="🔍 Search bikes..." value="${esc(bf.q)}" oninput="bf.q=this.value;updateGrid()">
      <select onchange="bf.sort=this.value;updateGrid()">
        <option value="featured" ${bf.sort==='featured'?'selected':''}>Sort: Featured</option>
        <option value="low" ${bf.sort==='low'?'selected':''}>Price: Low to High</option>
        <option value="high" ${bf.sort==='high'?'selected':''}>Price: High to Low</option>
        <option value="name" ${bf.sort==='name'?'selected':''}>Name A-Z</option>
      </select>
      <label class="check"><input type="checkbox" ${bf.avail?'checked':''} onchange="bf.avail=this.checked;updateGrid()"> Available only</label>
    </div>
    <div class="chips" style="margin-bottom:26px" id="chips">${types.map(t => `<button class="chip ${bf.type===t?'on':''}" onclick="bf.type='${t}';renderBrowse()">${t}</button>`).join('')}</div>
    <div class="bike-grid" id="bikeGrid"></div>`;
  updateGrid();
}
function updateGrid(){
  let list = getBikes().filter(b =>
    (bf.type === 'All' || b.type === bf.type) &&
    (!bf.avail || b.status === 'available') &&
    (b.name + b.type).toLowerCase().includes(bf.q.toLowerCase()));
  if (bf.sort === 'low') list.sort((a, b) => a.ratePerDay - b.ratePerDay);
  if (bf.sort === 'high') list.sort((a, b) => b.ratePerDay - a.ratePerDay);
  if (bf.sort === 'name') list.sort((a, b) => a.name.localeCompare(b.name));
  $('#bikeGrid').innerHTML = list.map(b => bikeCard(b)).join('') ||
    `<div class="empty" style="grid-column:1/-1"><div class="e">🚫</div><h3 style="font-size:2rem">No bikes found</h3><p>Try a different search or filter.</p></div>`;
}
function openDetails(id){
  const b = getBikes().find(x => x.id === id); if (!b) return;
  openModal(`<button class="x" onclick="closeModal()">✕</button>
    <h2>${esc(b.name)}</h2><span class="badge ${b.status}">${b.status}</span> <span class="badge" style="background:var(--navy);color:#fff">${esc(b.type)}</span>
    <div class="art" style="background:${tint(b.color)}">${bikeSVG(b.type, b.color)}</div>
    <p style="color:var(--muted);margin-bottom:14px">${esc(b.desc)}</p>
    <div class="specs"><span>⚙️ ${esc(b.gears)}</span><span>🔩 ${esc(b.frame)}</span><span>⚖️ ${esc(b.weight)}</span></div>
    <div class="preview"><div class="srow"><span>Per hour</span><b>${money(b.ratePerHour)}</b></div><div class="srow"><span>Per day</span><b>${money(b.ratePerDay)}</b></div><div class="srow" style="border:none"><span>Per week</span><b>${money(b.ratePerDay*7*.85)} <small style="color:var(--green)">(15% off)</small></b></div></div>
    <div class="modal-actions"><button class="btn btn-outline" onclick="closeModal()">Close</button>
    <button class="btn btn-primary" onclick="openRent('${b.id}')" ${b.status === 'available' ? '' : 'disabled'}>Rent This Bike</button></div>`);
}
function openRent(id){
  const b = getBikes().find(x => x.id === id); if (!b) return;
  if (b.status !== 'available') return toast('Sorry, this bike is not available.', 'error');
  const d = new Date(Date.now() + 15 * 60000); d.setMinutes(Math.ceil(d.getMinutes() / 15) * 15, 0, 0);
  openModal(`<button class="x" onclick="closeModal()">✕</button>
    <h2>RENT ${esc(b.name.toUpperCase())}</h2>
    <div class="art" style="background:${tint(b.color)}">${bikeSVG(b.type, b.color)}</div>
    <div class="field"><label>Pick-up date &amp; time</label><input type="datetime-local" id="rStart" value="${toLocalInput(d)}" min="${toLocalInput(new Date())}"></div>
    <div class="two">
      <div class="field"><label>Duration</label><input type="number" id="rQty" value="2" min="1" oninput="rentPreview('${b.id}')"></div>
      <div class="field"><label>Unit</label><select id="rUnit" onchange="rentPreview('${b.id}')"><option value="hours">Hours</option><option value="days">Days</option><option value="weeks">Weeks</option></select></div>
    </div>
    <div class="preview" id="rPrev"></div>
    <div class="modal-actions"><button class="btn btn-outline" onclick="closeModal()">Cancel</button><button class="btn btn-primary" onclick="addToCart('${b.id}')">🛒 Add to Cart</button></div>`);
  rentPreview(b.id);
}
function rentPreview(id){
  const b = getBikes().find(x => x.id === id), q = +$('#rQty').value, u = $('#rUnit').value;
  $('#rPrev').innerHTML = q > 0 ? `<div class="srow"><span>Rate</span><span>${u === 'hours' ? money(b.ratePerHour) + '/hr' : money(b.ratePerDay) + '/day'}</span></div>` + costRows(cost(b, q, u), u)
    : '<small style="color:var(--muted)">Enter a valid duration.</small>';
}
function checkDuration(q, u){
  if (!Number.isInteger(q) || q < 1) return 'Duration must be a whole number of at least 1.';
  if (q > UNIT_MAX[u]) return `Maximum is ${UNIT_MAX[u]} ${u} per rental.`;
  return '';
}
function addToCart(id){
  const start = $('#rStart').value, q = +$('#rQty').value, u = $('#rUnit').value;
  if (!start) return toast('Please choose a pick-up date and time.', 'error');
  if (new Date(start) < new Date(Date.now() - 5 * 60000)) return toast('Pick-up time cannot be in the past.', 'error');
  const err = checkDuration(q, u); if (err) return toast(err, 'error');
  const cart = getCart();
  if (cart.some(i => i.bikeId === id)) return toast('This bike is already in your cart. Edit it there.', 'warn');
  cart.push({bikeId:id, start:new Date(start).toISOString(), qty:q, unit:u}); saveCart(cart);
  closeModal(); toast('Added to cart 🛒'); renderUser();
}

/* ----- Cart ----- */
function renderCart(){
  const cart = cartValid(); saveCart(cart);
  const bikes = getBikes();
  if (!cart.length){
    $('#userContent').innerHTML = `<div class="page-head"><div><h1>YOUR CART</h1></div></div><div class="empty"><div class="e">🛒</div><h3 style="font-size:2rem;color:var(--navy)">Your cart is empty</h3><p style="margin-bottom:16px">Pick a bike to start your reservation.</p><button class="btn btn-primary" onclick="userNav('browse')">Browse Bikes</button></div>`;
    return;
  }
  let sub = 0, disc = 0;
  const items = cart.map((it, i) => {
    const b = bikes.find(x => x.id === it.bikeId), c = cost(b, it.qty, it.unit);
    sub += c.base; disc += c.discount;
    const end = new Date(new Date(it.start).getTime() + durMs(it.qty, it.unit));
    return `<div class="cart-item">
      <div style="background:${tint(b.color)};border-radius:14px;padding:6px">${bikeSVG(b.type, b.color)}</div>
      <div><h3>${esc(b.name)}</h3><span class="badge ${b.status}">${b.status}</span>
        <div class="opts">
          <div><label>Pick-up</label><input type="datetime-local" value="${toLocalInput(new Date(it.start))}" onchange="editCart(${i},'start',this.value)"></div>
          <div><label>Duration</label><input type="number" min="1" value="${it.qty}" onchange="editCart(${i},'qty',this.value)"></div>
          <div><label>Unit</label><select onchange="editCart(${i},'unit',this.value)">${['hours','days','weeks'].map(u => `<option ${u === it.unit ? 'selected' : ''}>${u}</option>`).join('')}</select></div>
        </div>
        <small style="color:var(--muted)">Return by ${fmt(end)}</small>
      </div>
      <div class="tot"><b>${money(c.total)}</b><button class="rm" onclick="removeCart(${i})">🗑 Remove</button></div></div>`;
  }).join('');
  $('#userContent').innerHTML = `
    <div class="page-head"><div><h1>YOUR CART</h1><p>Review and edit your selection before confirming.</p></div></div>
    <div class="cart-grid"><div>${items}</div>
    <div class="summary"><h3>ORDER SUMMARY</h3>
      <div class="srow"><span>Bikes</span><span>${cart.length}</span></div>
      <div class="srow"><span>Subtotal</span><span>${money(sub)}</span></div>
      <div class="srow"><span>Discounts</span><span>-${money(disc)}</span></div>
      <div class="srow total"><span>Total</span><b>${money(sub - disc)}</b></div>
      <div class="field" style="margin-top:16px"><label>Payment method</label><select id="payMethod"><option>Cash on pick-up</option><option>GCash</option><option>Credit / Debit Card</option></select></div>
      <button class="btn btn-lime" onclick="openCheckout()">Check Out →</button>
      <button class="btn btn-ghost" style="margin-top:8px" onclick="userNav('browse')">+ Add more bikes</button>
    </div></div>`;
}
function editCart(i, k, v){
  const cart = getCart(), it = cart[i];
  if (k === 'qty') v = Number(v);
  const next = {...it, [k]: v};
  if (k === 'start'){
    if (!v || new Date(v) < new Date(Date.now() - 5 * 60000)){ toast('Pick-up time cannot be in the past.', 'error'); return renderCart(); }
    next.start = new Date(v).toISOString();
  }
  const err = checkDuration(next.qty, next.unit);
  if (err){ toast(err, 'error'); return renderCart(); }
  cart[i] = next; saveCart(cart); renderCart();
}
function removeCart(i){ const c = getCart(); c.splice(i, 1); saveCart(c); toast('Removed from cart.'); renderUser(); }
function openCheckout(){
  const cart = cartValid(), bikes = getBikes(), pay = $('#payMethod').value;
  let total = 0;
  const lines = cart.map(it => { const b = bikes.find(x => x.id === it.bikeId), c = cost(b, it.qty, it.unit); total += c.total;
    return `<div class="srow"><span>${esc(b.name)}<br><small style="color:var(--muted)">${unitLabel(it.qty, it.unit)} · ${fmt(it.start)}</small></span><b>${money(c.total)}</b></div>`; }).join('');
  window.__pay = pay;
  openModal(`<button class="x" onclick="closeModal()">✕</button><h2>CONFIRM RESERVATION</h2>
    <p style="color:var(--muted);margin-bottom:10px">Please double-check your booking details.</p>
    <div class="preview">${lines}<div class="srow total"><span>Total</span><b>${money(total)}</b></div><div class="srow" style="border:none"><span>Payment</span><span>${esc(pay)}</span></div></div>
    <div class="modal-actions"><button class="btn btn-outline" onclick="closeModal()">Go back</button><button class="btn btn-primary" onclick="doCheckout()">✔ Confirm Booking</button></div>`);
}
function doCheckout(){
  const bikes = getBikes(), cart = getCart(), u = me(), txs = getTx(), made = [];
  const skipped = [];
  cart.forEach(it => {
    const b = bikes.find(x => x.id === it.bikeId);
    if (!b || b.status !== 'available'){ skipped.push(b ? b.name : 'A bike'); return; }
    const start = new Date(it.start), end = new Date(start.getTime() + durMs(it.qty, it.unit));
    const c = cost(b, it.qty, it.unit);
    const tx = {id:'BM-' + Date.now().toString(36).toUpperCase().slice(-5) + Math.floor(Math.random() * 90 + 10),
      userId:u.id, userName:u.name, userEmail:u.email, bikeId:b.id, bikeName:b.name, type:b.type, color:b.color,
      start:start.toISOString(), end:end.toISOString(), qty:it.qty, unit:it.unit, total:c.total, payment:window.__pay || 'Cash on pick-up',
      status:'Ongoing', created:Date.now()};
    txs.push(tx); made.push(tx); b.status = 'rented';
  });
  saveBikes(bikes); saveTx(txs); saveCart([]);
  if (!made.length){ closeModal(); toast('Those bikes are no longer available.', 'error'); return renderUser(); }
  if (skipped.length) toast('Skipped (unavailable): ' + skipped.join(', '), 'warn');
  openModal(`<div class="success"><div class="big">🎉</div><h2>RESERVATION CONFIRMED!</h2>
    <p style="color:var(--muted)">Your booking is saved. Show your code at the shop.</p>
    ${made.map(t => `<div class="code">${t.id}</div><div style="font-size:.85rem;color:var(--muted)">${esc(t.bikeName)} · ${money(t.total)}</div>`).join('')}
    <div class="modal-actions" style="justify-content:center"><button class="btn btn-primary" onclick="closeModal();userNav('rentals')">View My Rentals</button></div></div>`);
  renderUser();
}

/* ----- Rentals ----- */
function renderRentals(){
  const all = getTx().filter(t => t.userId === session.id).sort((a, b) => b.created - a.created);
  const act = all.filter(t => t.status === 'Ongoing'), spent = all.reduce((s, t) => s + t.total, 0);
  const list = all.filter(t => rf === 'all' || (rf === 'ongoing' ? t.status === 'Ongoing' : t.status === 'Completed'));
  $('#userContent').innerHTML = `
    <div class="page-head"><div><h1>MY RENTALS</h1><p>Your ongoing and past rentals.</p></div></div>
    <div class="stat-row"><div class="stat"><small>Total rentals</small><b>${all.length}</b></div><div class="stat"><small>Ongoing</small><b>${act.length}</b></div><div class="stat"><small>Completed</small><b>${all.length - act.length}</b></div><div class="stat"><small>Total spent</small><b>${money(spent)}</b></div></div>
    <div class="chips" style="margin-bottom:20px">${[['all','All'],['ongoing','Ongoing'],['completed','Completed']].map(([k, l]) => `<button class="chip ${rf === k ? 'on' : ''}" onclick="rf='${k}';renderRentals()">${l}</button>`).join('')}</div>
    ${list.map(t => {
      const left = new Date(t.end) - Date.now(), over = t.status === 'Ongoing' && left < 0;
      return `<div class="rental"><div style="background:${tint(t.color)};border-radius:14px;padding:4px">${bikeSVG(t.type, t.color)}</div>
        <div><h3>${esc(t.bikeName)} <span class="badge ${over ? 'overdue' : t.status.toLowerCase()}">${over ? 'Overdue' : t.status}</span></h3>
          <div class="meta">🔖 ${t.id} · ${esc(t.payment)}</div>
          <div class="meta">📅 ${fmt(t.start)} → ${fmt(t.end)} (${unitLabel(t.qty, t.unit)})</div>
          ${t.status === 'Ongoing' ? `<div class="meta" style="color:${over ? 'var(--red)' : 'var(--green)'};font-weight:600">⏱ ${over ? 'Overdue by ' + dur(left) : 'Due in ' + dur(left)}</div>` : `<div class="meta">✅ Returned ${fmt(t.returned || t.end)}</div>`}
        </div>
        <div class="right"><b>${money(t.total)}</b>${t.status === 'Ongoing' ? `<button class="btn btn-outline btn-sm" onclick="returnBike('${t.id}')">Return Bike</button>` : ''}</div></div>`;
    }).join('') || `<div class="empty"><div class="e">📭</div><h3 style="font-size:2rem;color:var(--navy)">No rentals here yet</h3><p style="margin-bottom:16px">Your rentals will show up after you check out.</p><button class="btn btn-primary" onclick="userNav('browse')">Rent a Bike</button></div>`}`;
}
function completeTx(id){
  const txs = getTx(), t = txs.find(x => x.id === id); if (!t || t.status !== 'Ongoing') return false;
  t.status = 'Completed'; t.returned = new Date().toISOString(); saveTx(txs);
  const bikes = getBikes(), b = bikes.find(x => x.id === t.bikeId);
  if (b && b.status === 'rented'){ b.status = 'available'; saveBikes(bikes); }
  return true;
}
function returnBike(id){
  confirmDialog('RETURN BIKE?', 'Mark this rental as returned. The bike becomes available for others.', 'Yes, return it', () => { completeTx(id); toast('Bike returned. Thanks for riding with BikeMo! 🚲'); renderRentals(); });
}

/* ============ ADMIN APP ============ */
let adminPage = 'dashboard';
let af = {bq:'', bt:'All', tq:'', ts:'all'};
function renderAdminShell(){
  const u = me(); if (!u || u.role !== 'admin') return goApp();
  $('#adminName').textContent = u.name;
  renderAdmin();
}
function adminNav(p){ adminPage = p; renderAdmin(); window.scrollTo(0, 0); }
function renderAdmin(){
  $('#adminNav').innerHTML = [['dashboard','📊 Dashboard'],['bikes','🚲 Manage Bikes'],['tx','🧾 Transactions'],['customers','👥 Customers']].map(([k, l]) =>
    `<button class="${adminPage === k ? 'on' : ''}" onclick="adminNav('${k}')">${l}</button>`).join('');
  ({dashboard:adminDashboard, bikes:adminBikes, tx:adminTx, customers:adminCustomers})[adminPage]();
}

/* ----- Dashboard ----- */
function adminDashboard(){
  const bikes = getBikes(), txs = getTx();
  const cnt = s => bikes.filter(b => b.status === s).length;
  const rev = txs.reduce((s, t) => s + t.total, 0);
  const types = ['Mountain','Road','City','Electric','Folding','BMX'];
  const byType = types.map(t => txs.filter(x => x.type === t).reduce((s, x) => s + x.total, 0));
  const max = Math.max(...byType, 1);
  const A = cnt('available'), R = cnt('rented'), M = cnt('maintenance'), N = bikes.length || 1;
  const a = A / N * 100, r = R / N * 100;
  $('#adminContent').innerHTML = `
    <div class="admin-top"><div><h1>DASHBOARD</h1><p style="color:var(--muted)">Overview of your bike rental business.</p></div><button class="btn btn-primary" onclick="adminNav('bikes');openBikeForm()">+ Add Bike</button></div>
    <div class="stat-row">
      <div class="stat"><small>Total bikes</small><b>${bikes.length}</b></div>
      <div class="stat"><small>Available</small><b>${A}</b></div>
      <div class="stat"><small>Rented</small><b>${R}</b></div>
      <div class="stat"><small>Revenue</small><b>${money(rev)}</b></div></div>
    <div class="grid2">
      <div class="panel"><h3>REVENUE BY BIKE TYPE</h3><div class="bars">${byType.map((v, i) => `<div class="bar"><b>${v ? money(v) : ''}</b><i style="height:${v / max * 150}px"></i>${types[i]}</div>`).join('')}</div></div>
      <div class="panel"><h3>FLEET STATUS</h3><div class="donut-wrap">
        <div class="donut" style="background:conic-gradient(var(--green) 0 ${a}%, var(--amber) ${a}% ${a + r}%, var(--red) ${a + r}% 100%)"></div>
        <div class="legend"><div><i style="background:var(--green)"></i>Available (${A})</div><div><i style="background:var(--amber)"></i>Rented (${R})</div><div><i style="background:var(--red)"></i>Maintenance (${M})</div></div></div></div>
    </div>
    <div class="panel"><h3>RECENT TRANSACTIONS</h3>${txTable(txs.slice().sort((a, b) => b.created - a.created).slice(0, 5), false)}</div>`;
}

/* ----- Manage bikes ----- */
function adminBikes(){
  $('#adminContent').innerHTML = `
    <div class="admin-top"><div><h1>MANAGE BIKES</h1><p style="color:var(--muted)">Add, edit, delete bikes and update their status.</p></div><button class="btn btn-primary" onclick="openBikeForm()">+ Add Bike</button></div>
    <div class="filters"><input type="text" placeholder="🔍 Search bikes..." value="${esc(af.bq)}" oninput="af.bq=this.value;bikeRows()">
      <select onchange="af.bt=this.value;bikeRows()">${['All','Mountain','Road','City','Electric','Folding','BMX'].map(t => `<option ${af.bt === t ? 'selected' : ''}>${t}</option>`).join('')}</select></div>
    <div class="panel"><div class="tbl-wrap"><table><thead><tr><th>Bike</th><th>Name</th><th>Type</th><th>Rate / hr</th><th>Rate / day</th><th>Status</th><th>Actions</th></tr></thead><tbody id="bikeRows"></tbody></table></div></div>`;
  bikeRows();
}
function bikeRows(){
  const list = getBikes().filter(b => (af.bt === 'All' || b.type === af.bt) && (b.name + b.type).toLowerCase().includes(af.bq.toLowerCase()));
  $('#bikeRows').innerHTML = list.map(b => `<tr>
    <td class="mini" style="background:${tint(b.color)}">${bikeSVG(b.type, b.color)}</td>
    <td><b>${esc(b.name)}</b><br><small style="color:var(--muted)">${esc(b.gears)} · ${esc(b.frame)}</small></td>
    <td>${esc(b.type)}</td><td>${money(b.ratePerHour)}</td><td>${money(b.ratePerDay)}</td>
    <td><select class="status-sel" onchange="setStatus('${b.id}',this.value)">${['available','rented','maintenance'].map(s => `<option value="${s}" ${b.status === s ? 'selected' : ''}>${s[0].toUpperCase() + s.slice(1)}</option>`).join('')}</select></td>
    <td><div class="acts"><button class="btn btn-outline btn-sm" onclick="openBikeForm('${b.id}')">Edit</button><button class="btn btn-danger btn-sm" onclick="deleteBike('${b.id}')">Delete</button></div></td></tr>`).join('')
    || `<tr><td colspan="7" style="text-align:center;padding:40px;color:var(--muted)">No bikes found.</td></tr>`;
}
function setStatus(id, s){
  const bikes = getBikes(), b = bikes.find(x => x.id === id); b.status = s; saveBikes(bikes);
  if (s !== 'rented'){
    const open = getTx().filter(t => t.bikeId === id && t.status === 'Ongoing');
    open.forEach(t => completeTx(t.id));
    const bs = getBikes(), bb = bs.find(x => x.id === id); bb.status = s; saveBikes(bs);
    if (open.length) toast('Active rental for this bike was marked as returned.', 'warn');
  }
  toast(`${b.name} is now ${s}.`); bikeRows();
}
function openBikeForm(id){
  const b = id ? getBikes().find(x => x.id === id) : {name:'', type:'City', color:'#ff6a1a', ratePerHour:'', ratePerDay:'', gears:'', frame:'', weight:'', desc:'', status:'available'};
  openModal(`<button class="x" onclick="closeModal()">✕</button><h2>${id ? 'EDIT BIKE' : 'ADD NEW BIKE'}</h2>
    <div class="field"><label>Bike name</label><input id="fName" value="${esc(b.name)}" placeholder="e.g. Trailblazer X1"></div>
    <div class="two"><div class="field"><label>Type</label><select id="fType">${['Mountain','Road','City','Electric','Folding','BMX'].map(t => `<option ${b.type === t ? 'selected' : ''}>${t}</option>`).join('')}</select></div>
      <div class="field"><label>Color</label><input id="fColor" type="color" value="${b.color}" style="height:46px;padding:4px"></div></div>
    <div class="two"><div class="field"><label>Rate per hour (₱)</label><input id="fHr" type="number" min="1" value="${b.ratePerHour}"></div>
      <div class="field"><label>Rate per day (₱)</label><input id="fDay" type="number" min="1" value="${b.ratePerDay}"></div></div>
    <div class="two"><div class="field"><label>Gears</label><input id="fGears" value="${esc(b.gears)}" placeholder="21-speed"></div>
      <div class="field"><label>Frame</label><input id="fFrame" value="${esc(b.frame)}" placeholder="Aluminum"></div></div>
    <div class="two"><div class="field"><label>Weight</label><input id="fWeight" value="${esc(b.weight)}" placeholder="12 kg"></div>
      <div class="field"><label>Status</label><select id="fStatus">${['available','rented','maintenance'].map(s => `<option value="${s}" ${b.status === s ? 'selected' : ''}>${s[0].toUpperCase() + s.slice(1)}</option>`).join('')}</select></div></div>
    <div class="field"><label>Description</label><textarea id="fDesc" rows="3">${esc(b.desc)}</textarea></div>
    <div class="modal-actions"><button class="btn btn-outline" onclick="closeModal()">Cancel</button><button class="btn btn-primary" onclick="saveBike('${id || ''}')">${id ? 'Save Changes' : 'Add Bike'}</button></div>`);
}
function saveBike(id){
  const v = k => $('#' + k).value.trim();
  const data = {name:v('fName'), type:v('fType'), color:v('fColor'), ratePerHour:+v('fHr'), ratePerDay:+v('fDay'),
    gears:v('fGears') || '—', frame:v('fFrame') || '—', weight:v('fWeight') || '—', desc:v('fDesc') || 'No description.', status:v('fStatus')};
  if (data.name.length < 2) return toast('Please enter the bike name.', 'error');
  if (!(data.ratePerHour > 0) || !(data.ratePerDay > 0)) return toast('Enter valid rates greater than 0.', 'error');
  const bikes = getBikes();
  if (id){ Object.assign(bikes.find(b => b.id === id), data); toast('Bike updated ✅'); }
  else { bikes.push({id:uid('b_'), ...data}); toast('Bike added 🚲'); }
  saveBikes(bikes); closeModal(); renderAdmin();
}
function deleteBike(id){
  const b = getBikes().find(x => x.id === id);
  if (getTx().some(t => t.bikeId === id && t.status === 'Ongoing')) return toast('This bike has an ongoing rental. Mark it returned first.', 'error');
  confirmDialog('DELETE BIKE?', `Remove <b>${esc(b.name)}</b> from the catalog? This cannot be undone.`, 'Delete', () => {
    saveBikes(getBikes().filter(x => x.id !== id)); toast('Bike deleted.'); renderAdmin();
  }, true);
}

/* ----- Transactions ----- */
function txTable(list, actions = true){
  if (!list.length) return `<p style="text-align:center;padding:30px;color:var(--muted)">No transactions yet.</p>`;
  return `<div class="tbl-wrap"><table><thead><tr><th>ID</th><th>Customer</th><th>Bike</th><th>Rental period</th><th>Total</th><th>Payment</th><th>Status</th>${actions ? '<th>Action</th>' : ''}</tr></thead><tbody>
    ${list.map(t => { const over = t.status === 'Ongoing' && new Date(t.end) < Date.now(); return `<tr>
      <td><b>${t.id}</b></td><td>${esc(t.userName)}<br><small style="color:var(--muted)">${esc(t.userEmail)}</small></td>
      <td>${esc(t.bikeName)}</td><td><small>${fmt(t.start)}<br>→ ${fmt(t.end)}<br>(${unitLabel(t.qty, t.unit)})</small></td>
      <td><b>${money(t.total)}</b></td><td>${esc(t.payment)}</td>
      <td><span class="badge ${over ? 'overdue' : t.status.toLowerCase()}">${over ? 'Overdue' : t.status}</span></td>
      ${actions ? `<td>${t.status === 'Ongoing' ? `<button class="btn btn-lime btn-sm" onclick="adminReturn('${t.id}')">Mark Returned</button>` : '—'}</td>` : ''}</tr>`; }).join('')}
    </tbody></table></div>`;
}
function adminTx(){
  $('#adminContent').innerHTML = `
    <div class="admin-top"><div><h1>TRANSACTIONS</h1><p style="color:var(--muted)">All rental transactions.</p></div></div>
    <div class="filters"><input type="text" placeholder="🔍 Search ID, customer or bike..." value="${esc(af.tq)}" oninput="af.tq=this.value;txRows()">
      <select onchange="af.ts=this.value;txRows()"><option value="all" ${af.ts==='all'?'selected':''}>All statuses</option><option value="Ongoing" ${af.ts==='Ongoing'?'selected':''}>Ongoing</option><option value="Completed" ${af.ts==='Completed'?'selected':''}>Completed</option></select></div>
    <div class="panel" id="txPanel"></div>`;
  txRows();
}
function txRows(){
  const q = af.tq.toLowerCase();
  const list = getTx().filter(t => (af.ts === 'all' || t.status === af.ts) && (t.id + t.userName + t.bikeName + t.userEmail).toLowerCase().includes(q)).sort((a, b) => b.created - a.created);
  const sum = list.reduce((s, t) => s + t.total, 0);
  $('#txPanel').innerHTML = `<p style="margin-bottom:12px;color:var(--muted)">${list.length} transaction(s) · Total <b style="color:var(--orange)">${money(sum)}</b></p>` + txTable(list);
}
function adminReturn(id){
  confirmDialog('MARK AS RETURNED?', 'The rental will be completed and the bike becomes available again.', 'Confirm', () => { completeTx(id); toast('Rental marked as returned.'); renderAdmin(); });
}

/* ----- Customers ----- */
function adminCustomers(){
  const txs = getTx(), users = getUsers().filter(u => u.role === 'user');
  $('#adminContent').innerHTML = `
    <div class="admin-top"><div><h1>CUSTOMERS</h1><p style="color:var(--muted)">Registered riders.</p></div></div>
    <div class="panel"><div class="tbl-wrap"><table><thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Rentals</th><th>Total spent</th><th>Joined</th></tr></thead><tbody>
    ${users.map(u => { const mine = txs.filter(t => t.userId === u.id); return `<tr><td><b>${esc(u.name)}</b></td><td>${esc(u.email)}</td><td>${esc(u.phone)}</td><td>${mine.length}</td><td>${money(mine.reduce((s, t) => s + t.total, 0))}</td><td>${new Date(u.created).toLocaleDateString('en-PH')}</td></tr>`; }).join('') || '<tr><td colspan="6" style="text-align:center;padding:30px;color:var(--muted)">No customers yet.</td></tr>'}
    </tbody></table></div></div>`;
}


// BikeMo - Customer Dashboard scripts

// Time-of-day greeting (uses the visitor's local clock)
(function () {
    var h = new Date().getHours();
    var g = h < 12 ? 'Good morning' : h < 18 ? 'Good afternoon' : 'Good evening';
    document.getElementById('bm-greeting').textContent = g;
})();

// Rotating ride tips
(function () {
    var tips = [
        'Adjust your saddle so your leg is almost fully extended at the bottom of the pedal stroke.',
        'Always check your brakes and tire pressure before you roll out.',
        'Wear a helmet and use lights when riding at dusk or in the evening.',
        'Stay hydrated: sip water every 15–20 minutes on longer rides.',
        'Signal early with your hand so drivers and other riders know your move.'
    ];
    var i = 0, el = document.getElementById('bm-tip-text');
    setInterval(function () {
        el.style.opacity = 0;
        setTimeout(function () {
            i = (i + 1) % tips.length;
            el.textContent = tips[i];
            el.style.opacity = 1;
        }, 400);
    }, 6000);
})();

/* ---------- Start --------    -- */
seed();

const heroBike = document.getElementById('heroBike');
if (heroBike) {
  heroBike.innerHTML = bikeSVG('Mountain', '#ff6a1a', true);
}
/* ---------- Auth pages (login.php, register.php) ---------- */
// Show / hide a password field. Defaults to the #password input.
function togglePassword(id = 'password') {
  const input = document.getElementById(id);
  if (input) input.type = input.type === 'password' ? 'text' : 'password';
}
/* ---------- Dashboard: count-up ng mga numero sa cards ---------- */
function bmCountUp(el) {
  const target = parseInt(el.dataset.bmCount, 10) || 0;
  if (target === 0 || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  const duration = 900, t0 = performance.now();
  el.textContent = '0';
  (function tick(now) {
    const p = Math.min((now - t0) / duration, 1);
    el.textContent = Math.round(target * (1 - Math.pow(1 - p, 3)));   // ease-out
    if (p < 1) requestAnimationFrame(tick);
  })(t0);
}
/* ---------- Customer dashboard (customers/dashboard.php) ---------- */
function initCustomerDashboard() {
  // Time-of-day greeting
  const greet = document.getElementById('bm-greeting');
  if (greet) {
    const h = new Date().getHours();
    greet.textContent = h < 12 ? 'Good morning' : h < 18 ? 'Good afternoon' : 'Good evening';
      document.querySelectorAll('[data-bm-count]').forEach(bmCountUp);
  }

  // Rotating ride tips
  const tipEl = document.getElementById('bm-tip-text');
  if (tipEl) {
    const tips = [
      'Adjust your saddle so your leg is almost fully extended at the bottom of the pedal stroke.',
      'Always check your brakes and tire pressure before you roll out.',
      'Wear a helmet and use lights when riding at dusk or in the evening.',
      'Stay hydrated: sip water every 15–20 minutes on longer rides.',
      'Signal early with your hand so drivers and other riders know your move.'
    ];
    let i = 0;
    setInterval(() => {
      tipEl.style.opacity = 0;
      setTimeout(() => {
        i = (i + 1) % tips.length;
        tipEl.textContent = tips[i];
        tipEl.style.opacity = 1;
      }, 400);
    }, 6000);
  }
}
document.addEventListener('DOMContentLoaded', initCustomerDashboard);

/* ---------- Start (landing page only) ---------- */
if ($('#view-landing')) {
  seed();

  const heroBike = document.getElementById('heroBike');
  if (heroBike) {
    heroBike.innerHTML = bikeSVG('Mountain', '#ff6a1a', true);
  }
  showView('landing');
  observeReveal();
}
showView('landing');
observeReveal(); 
showView('landing');
observeReveal();  

/* Shared customer-page interactions */
document.addEventListener('DOMContentLoaded', () => {
  const q = document.getElementById('bikeSearch');
  const type = document.getElementById('bikeType');
  if (q && type) {
    const filterBikes = () => document.querySelectorAll('.customer-bike').forEach(card => {
      card.hidden = !(card.dataset.name.includes(q.value.toLowerCase()) && (!type.value || card.dataset.type === type.value));
    });
    q.addEventListener('input', filterBikes);
    type.addEventListener('change', filterBikes);
  }
  const rentalFilter = document.getElementById('rentalFilter');
  if (rentalFilter) rentalFilter.addEventListener('change', () => {
    document.querySelectorAll('#rentalRows tr').forEach(row => row.hidden = !!rentalFilter.value && row.dataset.status !== rentalFilter.value);
  });
}); 
