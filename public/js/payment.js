// This is your test publishable API key.

const stripe = Stripe(
  "pk_test_51NDhzPSHyjX6emzkcceqxoF0ArODTR41UIcUvsqt8g8abJPoTBa5tz9jPsizVXVt7uan3kEo7jJRHOrJ75ymMQD700bfnuHkG3"
);

let emailAddress = document
  .getElementById("email-container")
  .getAttribute("data-email")
  .trim();
let getamount = document
  .getElementById("amount-container")
  .getAttribute("data-amount")
  .trim();
let loader = document.getElementById("loader");
let payment_form = document.getElementById("payment-form");

console.log("user email is " + emailAddress);
console.log("amount is " + getamount);

let elements;

initialize();
checkStatus();

document
  .querySelector("#payment-form")
  .addEventListener("submit", handleSubmit);

// Fetches a payment intent and captures the client secret
async function initialize() {
  const amountpost = [{ amount: getamount }];

  const { clientSecret } = await fetch("/stripepost", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ amountpost }),
  }).then((r) => r.json());

  console.log("clientSecret", clientSecret);

  elements = stripe.elements({ clientSecret });

  const linkAuthenticationElement = elements.create("linkAuthentication");
  linkAuthenticationElement.mount("#link-authentication-element");

  const paymentElementOptions = {
    layout: "tabs",
  };

  const paymentElement = elements.create("payment", paymentElementOptions);
  paymentElement.mount("#payment-element");

  loader.classList.add("d-none");
  payment_form.classList.remove("d-none");

}
async function handleSubmit(e) {
  e.preventDefault();
  setLoading(true);

  const { error } = await stripe.confirmPayment({
    elements,
    confirmParams: {
      return_url: "http://127.0.0.1:8000/stripesuccess",
      receipt_email: emailAddress,
    },
  });

  //if error while confirm payment
  if (error.type === "card_error" || error.type === "validation_error") {
    showMessage(error.message);
  } else {
    showMessage("An unexpected error occurred.");
  }

  setLoading(false);
}

// Fetches the payment intent status after payment submission
async function checkStatus() {
  const clientSecret = new URLSearchParams(window.location.search).get(
    "payment_intent_client_secret"
  );

  if (!clientSecret) {
    return;
  }

  const { paymentIntent } = await stripe.retrievePaymentIntent(clientSecret);

  switch (paymentIntent.status) {
    case "succeeded":
      console.log("Payment succeeded!");
      break;
    case "processing":
      console.log("Your payment is processing.");
      break;
    case "requires_payment_method":
      console.log("Your payment was not successful, please try again.");
      break;
    default:
      console.log("Something went wrong.");
      break;
  }
}

// ------- UI helpers -------

function showMessage(messageText) {
  const messageContainer = document.querySelector("#payment-message");

  messageContainer.classList.remove("hidden");
  messageContainer.textContent = messageText;

  setTimeout(function () {
    messageContainer.classList.add("hidden");
    messageText.textContent = "";
  }, 4000);
}

// Show a spinner on payment submission
function setLoading(isLoading) {
  if (isLoading) {
    // Disable the button and show a spinner
    document.querySelector("#submit").disabled = true;
    document.querySelector("#spinner").classList.remove("hidden");
    document.querySelector("#button-text").classList.add("hidden");
  } else {
    document.querySelector("#submit").disabled = false;
    document.querySelector("#spinner").classList.add("hidden");
    document.querySelector("#button-text").classList.remove("hidden");
  }
}
