<?php
if (!defined('ABSPATH')) {
	exit;
}
?>
<div class="consult-login-bar">
	<div class="consult-login-bar__inner">
		<div class="consult-login-bar__text">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
			<span><strong><?php esc_html_e('Existing patient?', 'woocommerce'); ?></strong> <?php esc_html_e('Log in to pre-fill your details and speed up your consultation.', 'woocommerce'); ?></span>
		</div>
		<div class="consult-login-bar__actions">
			<button type="button" class="consult-login-bar__btn consult-login-bar__btn--primary"><?php esc_html_e('Log In', 'woocommerce'); ?></button>
			<button type="button" class="consult-login-bar__btn consult-login-bar__btn--ghost"><?php esc_html_e('Create Account', 'woocommerce'); ?></button>
		</div>
	</div>
</div>

<section class="page-hero page-hero--consultation">
	<div class="hero-noise" aria-hidden="true"></div>
	<div class="container">
		<div class="breadcrumb breadcrumb--consultation">
			<a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'woocommerce'); ?></a>
			<span class="breadcrumb-sep" aria-hidden="true">&rsaquo;</span>
			<span class="breadcrumb-current"><?php esc_html_e('Consultation', 'woocommerce'); ?></span>
		</div>
		<h1><?php esc_html_e('Weight Loss Consultation', 'woocommerce'); ?></h1>
		<p><?php esc_html_e('Complete this short assessment so our prescribers can determine if treatment is right for you.', 'woocommerce'); ?></p>
		<div class="consult-banner__chip consult-hero-meta" role="note">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
			<?php esc_html_e('Takes approximately 2 minutes', 'woocommerce'); ?>
		</div>
	</div>
</section>

<div class="consult-layout">
  <div class="consult-grid">
      <!-- MAIN FORM -->
      <div class="consult-form">

        <!-- Step indicator -->
        <div class="step-indicator">
          <div class="step-ind active">
            <div class="step-ind-num">1</div>
            <div class="step-ind-label">Acknowledgement</div>
          </div>
          <div class="step-ind">
            <div class="step-ind-num">2</div>
            <div class="step-ind-label">About You</div>
          </div>
          <div class="step-ind">
            <div class="step-ind-num">3</div>
            <div class="step-ind-label">Medical History</div>
          </div>
          <div class="step-ind">
            <div class="step-ind-num">4</div>
            <div class="step-ind-label">Treatment</div>
          </div>
        </div>

        <!-- Progress bar -->
        <div class="progress-bar-wrap">
          <div class="progress-bar-info">
            <span class="progress-bar-label">Step <span id="currentStepNum">1</span> of 4</span>
            <span class="progress-bar-pct" id="progressPct">25%</span>
          </div>
          <div class="progress-bar-track">
            <div class="progress-bar-fill" id="progressFill" style="width:25%"></div>
          </div>
        </div>

        <!-- STEP 1: Acknowledgement -->
        <div class="form-section active" data-step="1">
          <div class="form-section-header">
            <div class="form-section-num">1</div>
            <h3>Acknowledgement</h3>
          </div>
          <p>These questions help us understand how you will use the medication we may prescribe for you.</p>

          <div class="form-note">
            <strong>Please note:</strong> If this is your first time taking this medication, you will only be allowed to order the initial strength. Following the initial dose, subsequent orders will be increased using a stepwise approach.
          </div>

          <div class="ack-list">
            <div class="ack-item">You are between 18–75 years old and live in the United Kingdom.</div>
            <div class="ack-item">You are completing this consultation on your own behalf and providing information to the best of your knowledge.</div>
            <div class="ack-item">This treatment is solely for your own use and you will only use one weight loss treatment at a time.</div>
            <div class="ack-item">You agree to read the Patient Information Leaflet and are aware of possible side effects.</div>
            <div class="ack-item">You will fully disclose all medical conditions, illnesses, surgical procedures, and current medications.</div>
            <div class="ack-item">You agree to your GP being informed (if prescribed) and grant our prescribers access to your medical history when necessary.</div>
            <div class="ack-item">You accept our Terms of Use, Terms of Sale, Cancellation &amp; Refund Policy, and Privacy Policy.</div>
          </div>

          <div class="checkbox-group">
            <label class="checkbox-item">
              <input type="checkbox"> I acknowledge and agree to all of the above
            </label>
          </div>

          <div class="step-nav">
            <span></span>
            <button class="btn-next" onclick="goToStep(2)">Next: About You <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg></button>
          </div>
        </div>

        <!-- STEP 2: About You -->
        <div class="form-section" data-step="2">
          <div class="form-section-header">
            <div class="form-section-num">2</div>
            <h3>About You &amp; Your Health</h3>
          </div>
          <p>These questions help us understand who you are and your current health status.</p>

          <div class="form-group">
            <label>Ethnic group <small>Optimal BMI ranges may vary based on ethnic background.</small></label>
            <select>
              <option value="">Select your ethnic group</option>
              <option>White</option>
              <option>Asian or Asian British</option>
              <option>Black or Black British</option>
              <option>Caribbean</option>
              <option>Mixed</option>
              <option>Arab</option>
              <option>Other</option>
            </select>
          </div>

          <div class="form-group">
            <label>Are you currently pregnant, trying to conceive, or breastfeeding?</label>
            <div class="radio-group">
              <label class="radio-pill"><input type="radio" name="pregnant" value="yes"> Yes</label>
              <label class="radio-pill"><input type="radio" name="pregnant" value="no"> No</label>
            </div>
          </div>

          <div class="form-group">
            <label>Your weight</label>
            <div class="unit-toggle">
              <button class="unit-btn active">kg</button>
              <button class="unit-btn">st / lbs</button>
            </div>
            <div class="form-row">
              <input type="number" placeholder="Weight in kg">
            </div>
          </div>

          <div class="form-group">
            <label>Your height</label>
            <div class="unit-toggle">
              <button class="unit-btn active">cm</button>
              <button class="unit-btn">ft / in</button>
            </div>
            <div class="form-row">
              <input type="number" placeholder="Height in cm">
            </div>
          </div>

          <div class="form-group">
            <label>Have you been diagnosed with diabetes? <small>This may affect how weight loss medication works for you.</small></label>
            <select>
              <option value="">Select an option</option>
              <option>I do not have diabetes</option>
              <option>I have pre-diabetes</option>
              <option>I have diabetes (diet controlled)</option>
              <option>I have diabetes (taking medication)</option>
            </select>
          </div>

          <div class="step-nav">
            <button class="btn-back" onclick="goToStep(1)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg> Back</button>
            <button class="btn-next" onclick="goToStep(3)">Next: Medical History <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg></button>
          </div>
        </div>

        <!-- STEP 3: Medical History -->
        <div class="form-section" data-step="3">
          <div class="form-section-header">
            <div class="form-section-num">3</div>
            <h3>Medical History</h3>
          </div>
          <p>Please select any conditions that apply to you. This helps our prescribers assess your suitability.</p>

          <div class="form-group">
            <label>Do any of the following apply to you?</label>
            <div class="checkbox-group">
              <label class="checkbox-item"><input type="checkbox"> Cancer</label>
              <label class="checkbox-item"><input type="checkbox"> Diabetic retinopathy</label>
              <label class="checkbox-item"><input type="checkbox"> Family history of thyroid cancer</label>
              <label class="checkbox-item"><input type="checkbox"> Heart conditions</label>
              <label class="checkbox-item"><input type="checkbox"> Kidney condition</label>
              <label class="checkbox-item"><input type="checkbox"> Liver disease</label>
              <label class="checkbox-item"><input type="checkbox"> Pancreatitis</label>
              <label class="checkbox-item"><input type="checkbox"> Active ulcerative colitis or Crohn's</label>
              <label class="checkbox-item"><input type="checkbox"> Cholestasis</label>
              <label class="checkbox-item"><input type="checkbox"> Chronic kidney disease</label>
              <label class="checkbox-item"><input type="checkbox"> None of the above</label>
            </div>
          </div>

          <div class="form-group">
            <label>Do you suffer from any other medical conditions?</label>
            <div class="radio-group">
              <label class="radio-pill"><input type="radio" name="other-conditions" value="yes"> Yes</label>
              <label class="radio-pill"><input type="radio" name="other-conditions" value="no"> No</label>
            </div>
          </div>

          <div class="form-group">
            <label>Are you currently taking any prescribed or over-the-counter medication?</label>
            <div class="radio-group">
              <label class="radio-pill"><input type="radio" name="medications" value="yes"> Yes</label>
              <label class="radio-pill"><input type="radio" name="medications" value="no"> No</label>
            </div>
          </div>

          <div class="form-group">
            <label>Are you allergic to any medicines or substances?</label>
            <div class="radio-group">
              <label class="radio-pill"><input type="radio" name="allergies" value="yes"> Yes</label>
              <label class="radio-pill"><input type="radio" name="allergies" value="no"> No</label>
            </div>
          </div>

          <div class="form-group">
            <label>Have you ever used any weight loss medication before?</label>
            <div class="radio-group">
              <label class="radio-pill"><input type="radio" name="prev-wl" value="yes"> Yes</label>
              <label class="radio-pill"><input type="radio" name="prev-wl" value="no"> No</label>
            </div>
          </div>

          <div class="step-nav">
            <button class="btn-back" onclick="goToStep(2)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg> Back</button>
            <button class="btn-next" onclick="goToStep(4)">Next: Treatment <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg></button>
          </div>
        </div>

        <!-- STEP 4: Treatment Preferences -->
        <div class="form-section" data-step="4">
          <div class="form-section-header">
            <div class="form-section-num">4</div>
            <h3>Treatment Preferences</h3>
          </div>

          <div class="form-group">
            <label>What is your target weight?</label>
            <div class="form-row">
              <input type="number" placeholder="Target weight (kg)">
            </div>
          </div>

          <div class="form-group">
            <label>Do you consume alcohol?</label>
            <select>
              <option value="">Select an option</option>
              <option>I do not consume alcohol</option>
              <option>1Ã¢â‚¬â€œ2 times per week</option>
              <option>3Ã¢â‚¬â€œ5 times per week</option>
              <option>Daily</option>
            </select>
          </div>

          <div class="form-group">
            <label>Do you smoke?</label>
            <div class="radio-group">
              <label class="radio-pill"><input type="radio" name="smoke" value="yes"> Yes</label>
              <label class="radio-pill"><input type="radio" name="smoke" value="no"> No</label>
            </div>
          </div>

          <div class="step-nav">
            <button class="btn-back" onclick="goToStep(3)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg> Back</button>
            <button class="btn-next btn-pulse" onclick="alert('Submitting consultation...')">Submit Consultation <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg></button>
          </div>
        </div>

      </div>

      <!-- SIDEBAR -->
      <div class="consult-sidebar">
        <div class="sidebar-card sidebar-card--progress">
          <h4>Assessment Progress</h4>
          <div class="progress-list">
            <div class="progress-step active"><div class="progress-step-num">1</div> Acknowledgement</div>
            <div class="progress-step"><div class="progress-step-num">2</div> About You &amp; Your Health</div>
            <div class="progress-step"><div class="progress-step-num">3</div> Medical History</div>
            <div class="progress-step"><div class="progress-step-num">4</div> Treatment Preferences</div>
          </div>
        </div>

        <div class="sidebar-card">
          <div class="sidebar-help">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <div>
              <strong>Need a helping hand?</strong><br>
              Give us a call on <strong>01onal 234 5678</strong> or <a href="#">Contact Us</a>
            </div>
          </div>
        </div>

        <div class="sidebar-card">
          <div class="sidebar-trust">
            <div class="sidebar-trust-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="20 6 9 17 4 12"/></svg> GPhC-registered pharmacy</div>
            <div class="sidebar-trust-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="20 6 9 17 4 12"/></svg> UK prescribers review every order</div>
            <div class="sidebar-trust-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="20 6 9 17 4 12"/></svg> Confidential &amp; discreet</div>
            <div class="sidebar-trust-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="20 6 9 17 4 12"/></svg> Next-day delivery available</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

