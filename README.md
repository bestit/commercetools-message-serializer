Just an very simple serializer for symfony messenger and CommerceTools messages.

### Step 1: Add package
`$ composer require best-it/commercetools-message-serializer`

### Step 2: Create service
```yaml
# services.yaml

services:
    BestIt\Messenger\CommerceToolsSerializer:
        class: BestIt\Messenger\CommerceToolsSerializer
```

### Step 3: Use service
```yaml
# messenger.yaml

framework:
    messenger:
        serializer:
            default_serializer: 'BestIt\Messenger\CommerceToolsSerializer'
```

Every message has a header parameter `X-CommerceTools-Message` which contains the full qualified class name of the
CommerceTools message (e.g.: `Commercetools\Core\Model\Message\OrderCreatedMessage`).