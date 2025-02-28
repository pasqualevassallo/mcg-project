#include <HTTPClient.h>
#include <WiFi.h>
#include <WebServer.h>

#define BUZZER_PIN 22
#define START_BUTTON_PIN 23
#define LEVELS 20
#define STATE_STOPPED 0
#define STATE_SHOW 1
#define STATE_REPEAT 2

String URL = "//insert";
String site = "//insert";
const char* ssid = "//insert";
const char* password = "//insert";

int buttons[4] = {32, 25, 14, 19};
int led[4] = {33, 26, 12, 18};
int notes[4] = {523, 587, 659, 698};
int sequence[LEVELS];
int level = 0;
int indice = 0;
int tempo = 150;
int state = STATE_STOPPED;
String username = "";
String difficolta = "";

WebServer server(80);

void setup() {
  Serial.begin(9600);
  connectWiFi();

  for (int i = 0; i < 4; i++) {
    pinMode(buttons[i], INPUT);
    pinMode(led[i], OUTPUT);
  }
  pinMode(START_BUTTON_PIN, INPUT);
  pinMode(BUZZER_PIN, OUTPUT);
  randomSeed(analogRead(2));

  // Imposta le rotte del server
  server.on("/", HTTP_GET, handleRoot);
  server.on("/send", HTTP_POST, handleSend);

  // Avvia il server
  server.begin();
}

void loop() {
  // Gestisce le richieste dei client
  server.handleClient();

  // Altri codici del loop principale
  if (digitalRead(START_BUTTON_PIN) == HIGH) {
    welcome();
  }
  if (state == STATE_SHOW) {
    showNextLevel();
  }
  if (state == STATE_REPEAT) {
    repeatSequence();
  }
  delay(10);
}

//FUNZIONI RICHIAMATE NEL LOOP
void connectWiFi() {
  WiFi.mode(WIFI_OFF);
  delay(1000);
  WiFi.mode(WIFI_STA); //configurazione in modalità station. L’ESP32 si comporta come un client Wi-Fi: può connettersi al router e comunicare con gli altri dispositivi nella rete

  WiFi.begin(ssid, password);
  Serial.println("Connecting to WiFi");

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.print("\nConnected to : ");
  Serial.print(ssid);
  Serial.print(", IP address : ");
  Serial.println(WiFi.localIP()); // Stampa l'indirizzo IP dell'ESP32
  Serial.println("Connettiti da qualunque dispositivo a questa rete WiFi ed inserisci il tuo username ed il livello di difficoltà al seguente IP address\n");
}

// Funzione per servire la pagina HTML
void handleRoot() {
  String html = "<!DOCTYPE html><html><head><title>MCG - Memory Color Game</title>"
                "<style>body{font-family:Arial, sans-serif;text-align:center;}"
                "form{margin:auto;width:50%;padding:20px;border:1px solid #ccc;border-radius:5px;background-color:#f9f9f9;}"
                "input[type='text'],select{width:100%;padding:10px;margin-bottom:10px;border:1px solid #ccc;border-radius:3px;box-sizing:border-box;}"
                "input[type='submit']{width:100%;padding:10px;border:none;border-radius:3px;background-color:#4CAF50;color:white;cursor:pointer;}"
                "</style></head>"
                "<body><h1>Benvenuto a MCG - Memory Color Game</h1>"
                "<h2>Inserisci Username e Livello di Difficolta'</h2>"
                "<form action='/send' method='POST'>"
                "Username: <input type='text' name='username'><br>"
                "Difficolta': <select name='difficolta'><option value='FACILE'>Facile</option><option value='DIFFICILE'>Difficile</option></select><br>"
                "<input type='submit' value='Invia'></form></body></html>";
  server.send(200, "text/html", html);
}

// Funzione per gestire i dati inviati dal form
void handleSend() {
  if (server.hasArg("username") && server.hasArg("difficolta")) {
    username = server.arg("username");
    difficolta = server.arg("difficolta");
    Serial.println("Received username: " + username);
    Serial.println("Received difficoltà: " + difficolta);
    Serial.println("Benvenuto " + username + ". Preparati a giocare nella modalità " + difficolta);

    // Imposta la difficoltà
    if (difficolta == "FACILE") {
      tempo = 150;
    } else if (difficolta == "DIFFICILE") {
      tempo = 75;
    } else {
      server.send(400, "text/plain", "Invalid difficulty level");
      return;
    }

    server.send(200, "text/plain", "Data received");
  } else {
    server.send(400, "text/plain", "Bad Request");
  }
}

void welcome() {
  int randomNumber;
  for (int i = 0; i < 7; i++) {
    randomNumber = random(4);
    tone(BUZZER_PIN, notes[randomNumber], 150);
    digitalWrite(led[randomNumber], HIGH);
    delay(75);
    digitalWrite(led[randomNumber], LOW);
    delay(75);
  }
  level = 0;
  state = STATE_SHOW;
  delay(3000);
}

void showNextLevel() {
  sequence[level] = random(4);
  for (int i = 0; i <= level; i++) {
    playNote(sequence[i]);
    delay(tempo);
  }
  indice = 0;
  level++;
  state = STATE_REPEAT;
}

void repeatSequence() {
  int selectedNote = readButtons();
  if (selectedNote >= 0) {
    if (selectedNote == sequence[indice]) {
      playNote(selectedNote);
      while (readButtons() != -1);
      indice++;
      if (indice >= level) {
        if (level < LEVELS) {
          state = STATE_SHOW;
          delay(1000);
        } else {
          win();
        }
      }
    } else {
      error(selectedNote);
    }
  }
}

void error(int note) {
  tone(BUZZER_PIN, 200, 1000);
  digitalWrite(led[note], HIGH);
  delay(1000);
  digitalWrite(led[note], LOW);
  sendData();
  state = STATE_STOPPED;
}

void win() {
  int randomNumber;
  delay(200);
  for (int i = 0; i < 12; i++) {
    randomNumber = random(4);
    tone(BUZZER_PIN, notes[randomNumber], 150);
    for (int j = 0; j < 4; j++) {
      digitalWrite(led[j], HIGH);
    }
    delay(75);
    for (int j = 0; j < 4; j++) {
      digitalWrite(led[j], LOW);
    }
    delay(75);
  }
  state = STATE_STOPPED;
}

void playNote(int note) {
  tone(BUZZER_PIN, notes[note], 150);
  digitalWrite(led[note], HIGH);
  delay(tempo);
  digitalWrite(led[note], LOW);
}

int readButtons() {
  for (int i = 0; i < 4; i++) {
    if (digitalRead(buttons[i]) == HIGH) {
      return i;
    }
  }
  return -1;
}

void sendData() {
  String userInfo = "Username = " + username + ", Difficoltà = " + difficolta + ", Punteggio = " + String(level - 1);
  Serial.println("\nRiepilogo partita ->  " + userInfo);
  Serial.println("Invio dati al database ... ");

  if (WiFi.status() == WL_CONNECTED) { // Controlla se sei ancora connesso al WiFi
    HTTPClient http;
    http.begin(URL);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    // Prepara i dati da inviare
    String httpRequestData = "Username=" + username + "&Difficoltà=" + difficolta + "&Punteggio=" + String(level - 1);

    // Effettua la richiesta POST
    int httpCode = http.POST(httpRequestData);
    String esito = http.getString();

    Serial.println("\nVisualizza storico punteggi : " + site);
    Serial.print("Esito : " + esito);
    http.end(); // Libera risorse

  } else {
    Serial.print("Connessione WiFi persa");
  }
  Serial.println("----------------------------------------------------------------------------");
}
