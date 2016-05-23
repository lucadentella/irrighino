// use physical switches
#define USE_SWITCHES     true

// debug output (enable/disable)
#define DEBUG_MODE
#ifdef DEBUG_MODE
  #define DEBUG_PRINT(x)  Console.print(x)
  #define DEBUG_PRINTLN(x)  Console.println(x)
#else
  #define DEBUG_PRINT(x)
  #define DEBUG_PRINTLN(x)
#endif

// outputs, digital PINs connected to the relays
#define OUT_1              5
#define OUT_2              4
#define OUT_3              3
#define OUT_4              2

// leds, digital PINs that directly drive the panel leds
#define LED_1              9
#define LED_2              8
#define LED_3              7
#define LED_4              6

// switches, analog PINs that read the 3-way panel switches
#define SWITCH_1           0
#define SWITCH_2           1
#define SWITCH_3           2
#define SWITCH_4           3

// rain sensor, digital PIN connected to the rain sensor
// (basically a 2-way switch, normally closed)
#define RAIN_SENSOR        10

// constants for switch positions
#define SWITCH_MANUAL_OFF  0
#define SWITCH_AUTO        1
#define SWITCH_MANUAL_ON   2

// constants for rain sensor status
#define NOT_RAINING        0
#define RAINING            1

