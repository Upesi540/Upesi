<template>
  <footer class="footer-panafrican q-pt-xl q-pb-md">
    <div class="container q-px-md">
      <div class="row q-col-gutter-xl">
        <div class="col-12 col-md-4">
          <div class="q-mb-md">
            <img :src="logoUrl" alt="Upesi Logo" class="logo-img">
          </div>
          <p class="text-grey-8 text-body1 line-height-1-6" style="max-width: 350px">
            L'infrastructure logistique et financière souveraine de l'agrobusiness africain.
            Propulsée depuis Lomé pour servir tout le continent.
          </p>
          <div class="row q-gutter-md q-mt-lg">
            <q-btn flat round dense icon="fa-brands fa-tiktok" class="social-btn" />
            <q-btn flat round dense icon="fa-brands fa-linkedin" class="social-btn" />
            <q-btn flat round dense icon="fa-brands fa-facebook" class="social-btn" />
          </div>
        </div>

        <div class="col-6 col-sm-3 col-md-3">
          <div class="text-overline text-weight-bold q-mb-lg text-primary">Assistance</div>
          <div class="column q-gutter-y-sm">
            <a href="mailto:contact@u-pesi.com" class="footer-link">
              <q-icon name="mail" class="q-mr-sm" /> contact@u-pesi.com
            </a>
            <a href="tel:+22896323223" class="footer-link">
              <q-icon name="phone" class="q-mr-sm" /> +228 96 32 32 23
            </a>
            <div class="footer-link opacity-70">
              <q-icon name="location_on" class="q-mr-sm" /> Siège : Lomé, Togo
            </div>
          </div>
        </div>

        <div class="col-12 col-md-5">
          <div class="text-overline text-weight-bold q-mb-md text-primary">Écosystème de paiement</div>

          <div class="payment-container">
            <div class="row items-center q-gutter-x-md q-mb-lg">
              <img src="https://raw.githubusercontent.com/aaronfagan/svg-credit-card-payment-icons/master/flat/visa.svg"
                alt="Visa" class="card-logo">
              <img src="https://raw.githubusercontent.com/aaronfagan/svg-credit-card-payment-icons/master/flat/mastercard.svg"
                alt="Mastercard" class="card-logo">
            </div>

            <div class="payment-grid">
              <div class="pay-badge orange">Orange Money</div>
              <div class="pay-badge mtn">MTN MoMo</div>
              <div class="pay-badge moov">Moov Money</div>
              <div class="pay-badge tmoney">T-Money</div>
              <div class="pay-badge wave">Wave</div>
              <div class="pay-badge airtel">Airtel Money</div>
              <div class="pay-badge celtiis">Celtiis</div>
              <div class="pay-badge coris">Coris Money</div>
              <div class="pay-badge bmo">BMO</div>
              <div>etc... </div>
            </div>
          </div>

          <div class="q-mt-lg text-caption text-grey-6 italic flex items-center">
            <q-icon name="lock" size="xs" class="q-mr-xs" />
            Sécurisé par cryptage SSL — Partenaires FedaPay
          </div>
        </div>
      </div>

      <q-separator class="q-my-xl opacity-10" />

      <div class="row justify-between items-center text-caption text-grey-7">
        <div class="col-12 col-sm-auto q-mb-sm">
          © {{ new Date().getFullYear() }} <strong>UPESI</strong> — Pan-African Logistique.
        </div>
        <div class="col-12 col-sm-auto row q-gutter-x-lg">
          <router-link :to="{ name: 'legal', params: { slug: 'terms' } }" class="bottom-link">Conditions</router-link>
          <router-link :to="{ name: 'legal', params: { slug: 'privacy' } }" class="bottom-link">Confidentialité</router-link>
          <router-link :to="{ name: 'legal', params: { slug: 'contracts' } }" class="bottom-link">Contrats</router-link>
        </div>
      </div>
    </div>
  </footer>
</template>
<script setup lang="ts">
import logoUrl from 'src/assets/logo.png';
</script>
<style lang="scss" scoped>
.footer-panafrican {
  background: #ffffff;
  color: #1d1d1d;
  border-top: 1px solid rgba(0, 0, 0, 0.08);
}

.logo-img {
  height: 44px;
  width: auto;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
}

.footer-link {
  color: #424242;
  text-decoration: none;
  font-size: 0.95rem;
  transition: color 0.3s;
  &:hover { color: var(--q-primary); }
}

.card-logo {
  height: 22px;
  width: auto;
  object-fit: contain;
}

.payment-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 6px; // Un peu plus serré pour le pro
}

.pay-badge {
  padding: 4px 10px;
  border-radius: 4px;
  font-size: 10px; // Légèrement réduit pour l'accumulation
  font-weight: 800;
  text-transform: uppercase;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid rgba(0,0,0,0.05);

  &.orange { background-color: #FF6600; }
  &.mtn { background-color: #FFCC00; color: #000; }
  &.moov { background-color: #004A99; }
  &.tmoney { background-color: #00A1E4; }
  &.wave { background-color: #1DCAFF; }
  &.airtel { background-color: #E11900; }
  &.celtiis { background-color: #7B2B91; }
  &.coris { background-color: #E30613; }
  &.bmo { background-color: #0056B3; }
}

.social-btn {
  background: #f5f5f5;
  color: #424242;
  &:hover { color: var(--q-primary); }
}

.bottom-link {
  color: inherit;
  text-decoration: none;
  &:hover { color: black; text-decoration: underline; }
}

.opacity-10 { opacity: 0.1; }
.opacity-70 { opacity: 0.7; }
</style>
