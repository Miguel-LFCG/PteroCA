# Earn Tokens Feature

## Overview

The Earn Tokens feature allows users to earn free tokens through alternative methods instead of paying. These tokens can be used in the same way as purchased tokens.

## Available Earning Methods

### 1. Watch Advertisements
- Users can watch video advertisements to earn tokens
- Configurable token amount per ad
- Rate-limited with configurable cooldown period (default: 60 minutes)
- **Note**: In production, this should be integrated with an ad provider (e.g., AdMob) to verify ad completion

### 2. Join Discord Server
- Users can earn tokens by joining your Discord server
- One-time reward per user
- Requires Discord OAuth2 configuration
- **Note**: In production, this should verify actual Discord server membership via Discord API

### 3. Daily Tasks
- Users can complete daily tasks to earn tokens
- Configurable token amount per task
- 24-hour cooldown between claims
- **Note**: Can be extended to verify specific task completion

## Admin Configuration

### Enable the Feature

1. Log in as an admin
2. Navigate to **Settings** → **Token Earning**
3. Enable "Enable Token Earning" checkbox
4. Configure the following settings:

#### General Settings
- **Enable Token Earning**: Enable/disable the entire feature

#### Ad Watch Settings
- **Ad Watch Token Amount**: Amount of tokens awarded for watching an ad (default: 1.00)
- **Ad Watch Cooldown (minutes)**: Minimum time between ad watches (default: 60)

#### Discord Settings
- **Discord Join Token Amount**: Amount of tokens for joining Discord (default: 5.00)
- **Discord Server ID**: Your Discord server/guild ID
- **Discord OAuth Client ID**: Discord application client ID
- **Discord OAuth Client Secret**: Discord application client secret
- **Discord Bot Token**: Bot token for verifying server membership

#### Task Settings
- **Task Completion Token Amount**: Amount of tokens for completing tasks (default: 2.00)

### Setting up Discord Integration

1. Create a Discord Application at https://discord.com/developers/applications
2. Add a bot to your application
3. Copy the Client ID, Client Secret, and Bot Token
4. Set the OAuth2 redirect URL to: `https://yourdomain.com/panel?routeName=earn_tokens_discord_callback`
5. Enable the following OAuth2 scopes: `identify`, `guilds.join`
6. Add the bot to your Discord server with appropriate permissions

## User Interface

Users can access the Earn Tokens page from the main menu. The page displays:
- Current token balance
- Three cards for each earning method (Ad, Discord, Task)
- Clear indication of token amounts and cooldowns
- Real-time balance updates without page refresh

## Viewing Token Earning Logs

Admins can view all token earning activities:
1. Navigate to **Logs** → **Token Earning Logs**
2. View detailed information including:
   - User who earned tokens
   - Method used
   - Amount earned
   - IP address
   - Timestamp
   - Additional details

## Security Features

### Rate Limiting
- Each earning method has its own cooldown period
- Prevents abuse by limiting how frequently users can claim tokens
- Discord rewards can only be claimed once per user

### Logging
- All token earning events are logged with:
  - User information
  - Method used
  - Amount earned
  - IP address
  - Timestamp
  - Additional context

### Validation
- Backend validation ensures all claims are legitimate
- IP addresses are recorded for audit purposes
- Database constraints prevent duplicate claims within cooldown periods

## Technical Implementation

### Database
- **token_earning_log** table tracks all earning events
- Indexed by user, method, and creation date for efficient queries
- Foreign key relationship with users table

### Entities
- `TokenEarningLog`: Represents a single token earning event
- `TokenEarningMethodEnum`: Enum for earning method types

### Services
- `TokenEarningService`: Main service handling all token earning logic
  - Rate limiting
  - Token awarding
  - Configuration management

### Controllers
- `EarnTokensController`: Handles UI and API endpoints
- `TokenEarningLogCrudController`: Admin interface for viewing logs
- `TokenEarningSettingCrudController`: Admin interface for configuration

## Future Enhancements

### For Production Use

1. **Ad Integration**: Integrate with real ad providers (AdMob, Unity Ads, etc.)
   - Verify ad completion through provider webhooks
   - Handle ad loading errors gracefully

2. **Discord Verification**: Implement full Discord API integration
   - Exchange OAuth code for access token
   - Verify user joined the specific server
   - Check for specific roles if needed

3. **Task System**: Expand task functionality
   - Define specific tasks users must complete
   - Verify task completion automatically
   - Support multiple task types

4. **Additional Earning Methods**:
   - Social media follows/shares
   - Referral program
   - Survey completion
   - Newsletter subscription

5. **Analytics Dashboard**:
   - Track earning method usage
   - Monitor abuse patterns
   - Generate reports on token distribution

## Troubleshooting

### Tokens Not Being Awarded
- Verify the feature is enabled in admin settings
- Check that token amounts are configured
- Ensure cooldown periods haven't been violated
- Review logs for error messages

### Discord Integration Not Working
- Verify Discord application credentials are correct
- Ensure redirect URL matches exactly
- Check that bot has proper permissions
- Confirm server ID is correct

### Rate Limiting Issues
- Review cooldown settings
- Check token earning logs for recent claims
- Clear cache if settings changes aren't reflected
